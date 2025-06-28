<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PenyewaanPerHari extends Component
{
    public $id_lokasi;
    public $tanggal_dipesan = [];
    public $tanggal_keranjang = [];
    public $deskripsi;
    public $lokasi;
    public $tarif;
    public $user;

    // Array of date ranges
    public $dateRanges = [];

    // Tambahkan key untuk mempertahankan komponen kalender
    public $kalenderKey;

    protected $rules = [
        'dateRanges.*.startDate' => 'required|date|after_or_equal:today',
        'dateRanges.*.endDate' => 'required|date|after_or_equal:dateRanges.*.startDate',
        'deskripsi' => 'nullable|string'
    ];

    protected $messages = [
        'dateRanges.*.startDate.required' => 'Tanggal mulai wajib diisi',
        'dateRanges.*.startDate.after_or_equal' => 'Tanggal mulai minimal hari ini',
        'dateRanges.*.endDate.required' => 'Tanggal selesai wajib diisi',
        'dateRanges.*.endDate.after_or_equal' => 'Tanggal selesai tidak boleh kurang dari tanggal mulai',
    ];

    protected $listeners = ['refreshCalendar' => 'loadTanggalDipesan'];

    public function mount($id_lokasi)
    {
        $this->id_lokasi = $id_lokasi;
        $this->kalenderKey = 'kalender-' . $id_lokasi . '-' . time(); // Unique key
        $this->loadTanggalDipesan();

        // Add initial date range
        $this->addDateRange();

        // Load lokasi data for tarif
        $this->lokasi = Lokasi::find($id_lokasi);
        if ($this->lokasi) {
            $this->tarif = $this->lokasi->tarif;
        } else {
            $this->tarif = 0;
        }
    }

    public function addDateRange()
    {
        $this->dateRanges[] = [
            'startDate' => null,
            'endDate' => null,
            'id' => count($this->dateRanges) + 1
        ];
    }

    public function removeDateRange($index)
    {
        if (count($this->dateRanges) > 1) {
            unset($this->dateRanges[$index]);
            $this->dateRanges = array_values($this->dateRanges);
        }
    }

    public function loadTanggalDipesan()
    {
        // Ambil semua tanggal yang sudah dipesan dengan status confirmed
        $penyewaan = Penyewaan::where('id_lokasi', $this->id_lokasi)
            ->where('kategori_sewa', 'per hari')
            ->whereIn('status',[ 'Pending','Confirmed']) 
            ->get();

        $this->tanggal_dipesan = [];

        foreach ($penyewaan as $sewa) {
            if (!empty($sewa->penyewaan_per_hari)) {
                foreach ($sewa->penyewaan_per_hari as $range) {
                    $start = Carbon::parse($range['tgl_mulai']);
                    $end = Carbon::parse($range['tgl_selesai']);

                    // Generate all dates in the range
                    for ($date = clone $start; $date->lte($end); $date->addDay()) {
                        $this->tanggal_dipesan[] = $date->format('Y-m-d');
                    }
                }
            }
        }

        // Load tanggal yang ada di keranjang secara terpisah
        $this->loadTanggalKeranjang();

        // Gabungkan semua tanggal yang tidak tersedia (confirmed + keranjang)
        $allBookedDates = array_unique(array_merge($this->tanggal_dipesan, $this->tanggal_keranjang));

        // Use dispatch instead of dispatchBrowserEvent for Livewire 3
        $this->dispatch('datesUpdated', bookedDates: $allBookedDates);
    }

    public function loadTanggalKeranjang()
    {
        $this->tanggal_keranjang = [];

        // Ambil tanggal dari keranjang session
        if (Session::has('keranjang')) {
            $keranjang = Session::get('keranjang');
            foreach ($keranjang as $item) {
                if ($item['id_lokasi'] == $this->id_lokasi && $item['kategori_sewa'] == 'per hari') {
                    foreach ($item['penyewaan_per_hari'] as $range) {
                        $start = Carbon::parse($range['tgl_mulai']);
                        $end = Carbon::parse($range['tgl_selesai']);

                        // Generate all dates in the range
                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            $this->tanggal_keranjang[] = $date->format('Y-m-d');
                        }
                    }
                }
            }
        }
    }

    public function updateDateRange()
    {
        // Validation will happen automatically
        $this->calculateTotalDays();
        $this->calculateSubTotal();
    }

    public function calculateTotalDays()
    {
        $totalDays = 0;

        foreach ($this->dateRanges as $range) {
            if (empty($range['startDate']) || empty($range['endDate'])) {
                continue;
            }

            $start = Carbon::parse($range['startDate']);
            $end = Carbon::parse($range['endDate']);

            // Ensure start date is not after end date
            if ($start->gt($end)) {
                continue;
            }

            // Calculate date difference properly, adding 1 to include both start and end dates
            $totalDays += $start->diffInDays($end) + 1;
        }

        return $totalDays;
    }

    public function calculateSubTotal()
    {
        $totalDays = $this->calculateTotalDays();
        return $totalDays * $this->tarif;
    }

    public function checkAvailability()
    {
        $this->validate();

        foreach ($this->dateRanges as $range) {
            if (empty($range['startDate']) || empty($range['endDate'])) {
                // Gunakan addError untuk error yang tidak akan menyebabkan redirect
                $this->addError('dateRanges', "Silakan pilih semua rentang tanggal terlebih dahulu.");
                return false;
            }

            $start = Carbon::parse($range['startDate']);
            $end = Carbon::parse($range['endDate']);

            if ($start->gt($end)) {
                $this->addError('dateRanges', "Tanggal mulai tidak boleh lebih besar dari tanggal selesai.");
                return false;
            }

            // Check if any selected date is already booked (confirmed bookings)
            for ($date = clone $start; $date->lte($end); $date->addDay()) {
                $currentDate = $date->format('Y-m-d');

                // Validasi 1: Cek tanggal yang sudah confirmed
                if (in_array($currentDate, $this->tanggal_dipesan)) {
                    $this->addError('dateRanges', "Tanggal {$currentDate} sudah dipesan. Silakan pilih tanggal lain.");
                    return false;
                }

                // Validasi 2: Cek tanggal yang sudah ada di keranjang
                if (in_array($currentDate, $this->tanggal_keranjang)) {
                    $this->addError('dateRanges', "Tanggal {$currentDate} sudah ada di keranjang. Silakan pilih tanggal lain atau hapus item keranjang terlebih dahulu.");
                    return false;
                }
            }

            // Check for overlapping ranges within the current selection
            foreach ($this->dateRanges as $compareRange) {
                // Skip comparing the same range
                if ($range === $compareRange) {
                    continue;
                }

                if (empty($compareRange['startDate']) || empty($compareRange['endDate'])) {
                    continue;
                }

                $compareStart = Carbon::parse($compareRange['startDate']);
                $compareEnd = Carbon::parse($compareRange['endDate']);

                // Check if ranges overlap
                if (($start->between($compareStart, $compareEnd) ||
                    $end->between($compareStart, $compareEnd) ||
                    ($start->lte($compareStart) && $end->gte($compareEnd)))) {
                    $this->addError('dateRanges', "Rentang tanggal tidak boleh tumpang tindih.");
                    return false;
                }
            }
        }

        return true;
    }

    public function simpanKeKeranjang()
    {
        // Check if we have at least one date range
        if (count($this->dateRanges) < 1) {
            $this->addError('dateRanges', 'Silakan pilih setidaknya satu rentang tanggal.');
            return;
        }

        $this->validate();

        // Refresh data keranjang sebelum validasi untuk memastikan data terbaru
        $this->loadTanggalKeranjang();

        if (!$this->checkAvailability()) {
            return;
        }

        // Pastikan tarif tidak null
        if ($this->tarif === null) {
            // Cek kembali tarif dari database jika masih null
            if ($this->lokasi) {
                $this->tarif = $this->lokasi->tarif;
            }

            // Jika masih null, set ke 0 untuk menghindari error
            if ($this->tarif === null) {
                $this->tarif = 0;
            }
        }

        // Create cleaned date ranges array
        $cleanRanges = [];
        foreach ($this->dateRanges as $range) {
            if (!empty($range['startDate']) && !empty($range['endDate'])) {
                $cleanRanges[] = [
                    'tgl_mulai' => $range['startDate'],
                    'tgl_selesai' => $range['endDate']
                ];
            }
        }

        $totalDays = $this->calculateTotalDays();
        $subTotal = $this->calculateSubTotal();

        try {
            // Buat item keranjang
            $keranjangItem = [
                'id' => uniqid(),
                'id_lokasi' => $this->id_lokasi,
                'nama_tempat' => $this->lokasi->tempat->nama ?? 'Nama Tempat Tidak Ada',
                'nama_lokasi' => $this->lokasi->nama_lokasi,
                'kategori_sewa' => 'per hari',
                'tgl_booking' => now()->format('Y-m-d'),
                'penyewaan_per_hari' => $cleanRanges,
                'deskripsi' => $this->deskripsi,
                'total_durasi' => $totalDays,
                'tarif' => $this->tarif,
                'sub_total' => $subTotal,
            ];

            // Ambil keranjang dari session atau buat baru jika belum ada
            $keranjang = Session::get('keranjang', []);

            // Tambahkan item ke keranjang
            $keranjang[] = $keranjangItem;

            // Simpan kembali ke session
            Session::put('keranjang', $keranjang);

            // Gunakan session flash success untuk redirect
            session()->flash('success', 'Pemesanan berhasil ditambahkan ke keranjang! Total pemesanan: ' . $totalDays .
                ' hari dengan biaya Rp ' . number_format($subTotal, 0, ',', '.') . ', Silakan melakukan checkout!');

            // Redirect ke keranjang
            return redirect()->route('keranjang');
        } catch (\Exception $e) {
            $this->addError('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.penyewaan-per-hari', [
            'totalHari' => $this->calculateTotalDays(),
            'subTotal' => $this->calculateSubTotal()
        ]);
    }
}

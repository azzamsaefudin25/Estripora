<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PenyewaanPerJam extends Component
{
    public $id_lokasi;
    public $jam_dipesan = [];
    public $deskripsi;
    public $lokasi;
    public $tarif;
    public $user;

    // Array of hour ranges
    public $hourRanges = [];

    // Available hours options
    public $availableHours = [];
    public $endHoursOptions = [];

    protected $rules = [
        'hourRanges.*.date' => 'required|date|after_or_equal:today',
        'hourRanges.*.startHour' => 'required|integer|min:0|max:23',
        'hourRanges.*.endHour' => 'required|integer|min:0|max:23|gt:hourRanges.*.startHour',
        'deskripsi' => 'nullable|string'
    ];

    protected $messages = [
        'hourRanges.*.date.required' => 'Tanggal wajib diisi',
        'hourRanges.*.date.after_or_equal' => 'Tanggal minimal hari ini',
        'hourRanges.*.startHour.required' => 'Jam mulai wajib diisi',
        'hourRanges.*.startHour.integer' => 'Jam mulai harus berupa angka',
        'hourRanges.*.startHour.min' => 'Jam mulai minimal 0',
        'hourRanges.*.startHour.max' => 'Jam mulai maksimal 23',
        'hourRanges.*.endHour.required' => 'Jam selesai wajib diisi',
        'hourRanges.*.endHour.integer' => 'Jam selesai harus berupa angka',
        'hourRanges.*.endHour.min' => 'Jam selesai minimal 0',
        'hourRanges.*.endHour.max' => 'Jam selesai maksimal 23',
        'hourRanges.*.endHour.gt' => 'Jam selesai harus lebih besar dari jam mulai',
    ];


    protected $listeners = ['refreshCalendar' => 'loadJamDipesan'];

    public function mount($id_lokasi)
    {
        $this->id_lokasi = $id_lokasi;
        $this->loadJamDipesan();

        // Add initial hour range
        $this->addHourRange();

        // Load lokasi data for tarif
        $this->lokasi = Lokasi::find($id_lokasi);
        if ($this->lokasi) {
            $this->tarif = $this->lokasi->tarif;
        } else {
            $this->tarif = 0;
        }
    }

    public function addHourRange()
    {
        $index = count($this->hourRanges);
        $this->hourRanges[] = [
            'date' => null,
            'startHour' => null,
            'endHour' => null,
            'id' => $index + 1
        ];

        // Initialize available hours for new range
        $this->availableHours[$index] = range(0, 23);
        $this->endHoursOptions[$index] = [];
    }

    public function removeHourRange($index)
    {
        if (count($this->hourRanges) > 1) {
            unset($this->hourRanges[$index]);
            unset($this->availableHours[$index]);
            unset($this->endHoursOptions[$index]);

            // Re-index arrays
            $this->hourRanges = array_values($this->hourRanges);
            $this->availableHours = array_values($this->availableHours);
            $this->endHoursOptions = array_values($this->endHoursOptions);
        }
    }

    public function loadJamDipesan()
    {
        // Ambil semua jam yang sudah dipesan (per jam)
        $penyewaan = Penyewaan::where('id_lokasi', $this->id_lokasi)
            ->where('kategori_sewa', 'per jam')
            ->where('status', 'Confirmed')
            ->get();

        $this->jam_dipesan = [];

        foreach ($penyewaan as $sewa) {
            if (!empty($sewa->penyewaan_per_jam)) {
                foreach ($sewa->penyewaan_per_jam as $range) {
                    $date = $range['tgl_mulai'];
                    $startHour = (int)substr($range['jam_mulai'], 0, 2);
                    $endHour = (int)substr($range['jam_selesai'], 0, 2);

                    // Generate all hours in the range
                    for ($hour = $startHour; $hour < $endHour; $hour++) {
                        $this->jam_dipesan[] = [
                            'date' => $date,
                            'hour' => $hour
                        ];
                    }
                }
            }
        }

        // Tambahkan pengecekan jam dari keranjang juga
        if (Session::has('keranjang')) {
            $keranjang = Session::get('keranjang');
            foreach ($keranjang as $item) {
                if ($item['id_lokasi'] == $this->id_lokasi && $item['kategori_sewa'] == 'per jam') {
                    foreach ($item['penyewaan_per_jam'] as $range) {
                        $date = $range['tgl_mulai'];
                        $startHour = (int)substr($range['jam_mulai'], 0, 2);
                        $endHour = (int)substr($range['jam_selesai'], 0, 2);

                        // Generate all hours in the range
                        for ($hour = $startHour; $hour < $endHour; $hour++) {
                            $this->jam_dipesan[] = [
                                'date' => $date,
                                'hour' => $hour
                            ];
                        }
                    }
                }
            }
        }

        // Update available hours for existing ranges
        foreach ($this->hourRanges as $index => $range) {
            if (!empty($range['date'])) {
                $this->updateHourAvailability($index);
            }
        }
    }

    public function updateHourAvailability($index)
    {
        $date = $this->hourRanges[$index]['date'];

        if (empty($date)) {
            $this->availableHours[$index] = [];
            $this->endHoursOptions[$index] = [];
            return;
        }

        // Initialize available hours (0-23)
        $hours = range(0, 23);

        // // Remove booked hours for the selected date
        // foreach ($this->jam_dipesan as $bookedTime) {
        //     if ($bookedTime['date'] == $date) {
        //         $key = array_search($bookedTime['hour'], $hours);
        //         if ($key !== false) {
        //             unset($hours[$key]);
        //         }
        //     }
        // }

        $this->availableHours[$index] = array_values($hours);

        // Update end hour options if start hour is selected
        $this->updateEndHourOptions($index);
    }

    public function updateEndHourOptions($index)
    {
        $startHour = $this->hourRanges[$index]['startHour'];
        $date = $this->hourRanges[$index]['date'];

        if (empty($startHour) || empty($date)) {
            $this->endHoursOptions[$index] = [];
            return;
        }

        // Get hours after start hour
        $endOptions = range($startHour + 1, 24);

        // Check for next booked hour to limit selection
        $bookedHoursAfterStart = [];
        foreach ($this->jam_dipesan as $bookedTime) {
            if ($bookedTime['date'] == $date && $bookedTime['hour'] > $startHour) {
                $bookedHoursAfterStart[] = $bookedTime['hour'];
            }
        }

        if (!empty($bookedHoursAfterStart)) {
            $nextBookedHour = min($bookedHoursAfterStart);
            // Limit end options up to next booked hour
            $endOptions = array_filter($endOptions, function ($hour) use ($nextBookedHour) {
                return $hour <= $nextBookedHour;
            });
        }

        $this->endHoursOptions[$index] = array_values($endOptions);
    }

    public function updateHourRange()
    {
        // Update end hour options for all ranges when any range changes
        foreach ($this->hourRanges as $index => $range) {
            if (!empty($range['date']) && !empty($range['startHour'])) {
                $this->updateEndHourOptions($index);
            }
        }

        $this->calculateTotalHours();
        $this->calculateSubTotal();
    }

    public function calculateTotalHours()
    {
        $totalHours = 0;

        foreach ($this->hourRanges as $range) {
            if (empty($range['date']) || empty($range['startHour']) || empty($range['endHour'])) {
                continue;
            }

            // Calculate hour difference
            $totalHours += $range['endHour'] - $range['startHour'];
            if ($range['startHour'] > $range['endHour']) {
                $totalHours = 0;
            }
        }

        return $totalHours;
    }

    public function calculateSubTotal()
    {
        $totalHours = $this->calculateTotalHours();
        return $totalHours * $this->tarif;
    }

    public function checkAvailability()
    {
        $this->validate();

        foreach ($this->hourRanges as $range) {
            if (empty($range['date']) || empty($range['startHour']) || empty($range['endHour'])) {
                session()->flash('error', "Silakan pilih semua rentang jam terlebih dahulu.");
                return false;
            }

            $date = $range['date'];
            $startHour = $range['startHour'];
            $endHour = $range['endHour'];

            // Check if all hours in the range are available
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                foreach ($this->jam_dipesan as $bookedTime) {
                    if ($bookedTime['date'] == $date && $bookedTime['hour'] == $hour) {
                       $this->addError('hourRanges', "Jam " . sprintf('%02d:00', $hour) . " pada tanggal " . Carbon::parse($date)->format('d M Y') . " sudah dipesan. Silakan pilih jam lain.");
                        return false;
                    }
                }
            }

            // Check for overlapping ranges within the current selection
            foreach ($this->hourRanges as $compareRange) {
                // Skip comparing the same range
                if ($range === $compareRange) {
                    continue;
                }

                if (empty($compareRange['date']) || empty($compareRange['startHour']) || empty($compareRange['endHour'])) {
                    continue;
                }

                // Only check for overlaps on the same date
                if ($range['date'] === $compareRange['date']) {
                    $rangeStart = $range['startHour'];
                    $rangeEnd = $range['endHour'];
                    $compareStart = $compareRange['startHour'];
                    $compareEnd = $compareRange['endHour'];

                    // Check if ranges overlap
                    if (($rangeStart < $compareEnd && $rangeEnd > $compareStart)) {
                        session()->flash('error', "Rentang jam tidak boleh tumpang tindih.");
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function simpanKeKeranjang()
    {
        // Check if we have at least one hour range
        if (count($this->hourRanges) < 1) {
            session()->flash('error', 'Silakan pilih setidaknya satu rentang jam.');
            return;
        }

        $this->validate();

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

        // Create cleaned hour ranges array
        $cleanRanges = [];
        foreach ($this->hourRanges as $range) {
            if (!empty($range['date']) && !empty($range['startHour']) && !empty($range['endHour'])) {
                $cleanRanges[] = [
                    'tgl_mulai' => $range['date'],
                    'jam_mulai' => sprintf('%02d:00:00', $range['startHour']),
                    'jam_selesai' => sprintf('%02d:00:00', $range['endHour'])
                ];
            }
        }

        $totalHours = $this->calculateTotalHours();
        $subTotal = $this->calculateSubTotal();

        try {
            // Buat item keranjang
            $keranjangItem = [
                'id' => uniqid(), // ID unik untuk item keranjang
                'id_lokasi' => $this->id_lokasi,
                'nama_tempat' => $this->lokasi->tempat->nama ?? 'Nama Tempat Tidak Ada',
                'nama_lokasi' => $this->lokasi->nama_lokasi,
                'kategori_sewa' => 'per jam',
                'tgl_booking' => now()->format('Y-m-d'),
                'penyewaan_per_jam' => $cleanRanges,
                'deskripsi' => $this->deskripsi,
                'total_durasi' => $totalHours,
                'tarif' => $this->tarif,
                'sub_total' => $subTotal,
            ];

            // Ambil keranjang dari session atau buat baru jika belum ada
            $keranjang = Session::get('keranjang', []);

            // Tambahkan item ke keranjang
            $keranjang[] = $keranjangItem;

            // Simpan kembali ke session
            Session::put('keranjang', $keranjang);

            session()->flash('success', 'Pemesanan berhasil ditambahkan ke keranjang! Total pemesanan: ' . $totalHours .
                ' jam dengan biaya Rp ' . number_format($subTotal, 0, ',', '.'). ', Silakan melakukan checkout!');

            return redirect()->route('keranjang');
            // Reset form
            $this->deskripsi = '';
            // Reset to a single empty hour range
            $this->hourRanges = [];
            $this->availableHours = [];
            $this->endHoursOptions = [];
            $this->addHourRange();

            $this->loadJamDipesan();

            // Perbarui jumlah item di keranjang (bisa digunakan untuk badge notifikasi)
            $this->dispatch('keranjangUpdated', count: count($keranjang));
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.penyewaan-per-jam', [
            'totalJam' => $this->calculateTotalHours(),
            'subTotal' => $this->calculateSubTotal()
        ]);
    }
}

<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\Penyewaan;
use App\Models\Transaksi; // Add this import
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB; // Add this import

class Keranjang extends Component
{
    public $keranjangItems = [];
    public $totalKeseluruhan = 0;

    protected $listeners = ['keranjangUpdated' => 'refreshKeranjang'];

    public function mount()
    {
        $this->refreshKeranjang();
    }

    public function refreshKeranjang()
    {
        $this->keranjangItems = Session::get('keranjang', []);
        $this->hitungTotal();
    }

    public function hitungTotal()
    {
        $this->totalKeseluruhan = 0;
        foreach ($this->keranjangItems as $item) {
            $this->totalKeseluruhan += $item['sub_total'];
        }
    }

    public function hapusItem($index)
    {
        // Ambil keranjang dari session
        $keranjang = Session::get('keranjang', []);

        // Pastikan index valid
        if (isset($keranjang[$index])) {
            // Hapus item dari array
            unset($keranjang[$index]);

            // Re-index array setelah penghapusan
            $keranjang = array_values($keranjang);

            // Simpan kembali ke session
            Session::put('keranjang', $keranjang);

            // Refresh data komponen
            $this->keranjangItems = $keranjang;
            $this->hitungTotal();

            // Perbarui jumlah item di keranjang (untuk badge notifikasi)
            $this->dispatch('keranjangUpdated', count: count($keranjang));

            session()->flash('message', 'Item berhasil dihapus dari keranjang.');
        }
    }

    /**
     * Calculate expiration time for payment (24 hours from now)
     */
    private function calculateExpirationTime()
    {
        return now()->addHours(24);
    }

    /**
     * Prepare detail penyewaan for transaction record
     */
    private function prepareDetailPenyewaan($item)
    {
        $detail = [
            'nama_lokasi' => $item['nama_lokasi'],
            'kategori_sewa' => $item['kategori_sewa'],
            'deskripsi' => $item['deskripsi'],
            'tarif' => $item['tarif'],
            'total_durasi' => $item['total_durasi'],
            'sub_total' => $item['sub_total']
        ];

        // Add specific rental details based on category
        if ($item['kategori_sewa'] == 'per hari') {
            $detail['penyewaan_per_hari'] = $item['penyewaan_per_hari'];
        } elseif ($item['kategori_sewa'] == 'per jam') {
            $detail['penyewaan_per_jam'] = $item['penyewaan_per_jam'];
        }

        // Add area info if available
        if (isset($item['luas'])) {
            $detail['luas'] = $item['luas'];
        }

        return $detail;
    }

    private function checkDuplicateBooking($item, $userId)
    {
        // Query untuk mencari penyewaan yang sudah ada dengan kondisi yang sama
        $existingBooking = Penyewaan::where('id_user', $userId)
            ->where('id_lokasi', $item['id_lokasi'])
            ->where('kategori_sewa', $item['kategori_sewa'])
            ->whereNotIn('status', ['Canceled'])
            ->get();

        if ($item['kategori_sewa'] == 'per hari') {
            // Untuk penyewaan per hari, cek apakah ada tanggal yang tumpang tindih
            foreach ($existingBooking as $existing) {
                if (!empty($existing->penyewaan_per_hari)) {
                    // Ambil semua tanggal dari item keranjang
                    $cartDates = [];
                    foreach ($item['penyewaan_per_hari'] as $range) {
                        $start = Carbon::parse($range['tgl_mulai']);
                        $end = Carbon::parse($range['tgl_selesai']);

                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            $cartDates[] = $date->format('Y-m-d');
                        }
                    }

                    // Ambil semua tanggal dari booking yang sudah ada
                    $existingDates = [];
                    foreach ($existing->penyewaan_per_hari as $existingRange) {
                        $start = Carbon::parse($existingRange['tgl_mulai']);
                        $end = Carbon::parse($existingRange['tgl_selesai']);

                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            $existingDates[] = $date->format('Y-m-d');
                        }
                    }

                    // Cek apakah ada tanggal yang sama
                    $intersection = array_intersect($cartDates, $existingDates);
                    if (!empty($intersection)) {
                        return [
                            'isDuplicate' => true,
                            'conflictDates' => $intersection
                        ];
                    }
                }
            }
        } elseif ($item['kategori_sewa'] == 'per jam') {
            // Untuk penyewaan per jam, cek apakah ada waktu yang tumpang tindih
            foreach ($existingBooking as $existing) {
                if (!empty($existing->penyewaan_per_jam)) {
                    // Cek apakah ada konflik jam pada tanggal yang sama
                    foreach ($item['penyewaan_per_jam'] as $cartJam) {
                        // Pastikan key yang diperlukan ada
                        if (!isset($cartJam['tgl_mulai'], $cartJam['jam_mulai'], $cartJam['jam_selesai'])) {
                            continue; // Skip jika struktur data tidak sesuai
                        }

                        foreach ($existing->penyewaan_per_jam as $existingJam) {
                            // Pastikan key yang diperlukan ada di data existing
                            if (!isset($existingJam['tgl_mulai'], $existingJam['jam_mulai'], $existingJam['jam_selesai'])) {
                                continue; // Skip jika struktur data tidak sesuai
                            }

                            // Cek apakah tanggal sama
                            if ($cartJam['tgl_mulai'] === $existingJam['tgl_mulai']) {
                                try {
                                    // Parse waktu dengan lebih robust
                                    $cartStartTime = $this->parseTimeString($cartJam['jam_mulai']);
                                    $cartEndTime = $this->parseTimeString($cartJam['jam_selesai']);
                                    $existingStartTime = $this->parseTimeString($existingJam['jam_mulai']);
                                    $existingEndTime = $this->parseTimeString($existingJam['jam_selesai']);

                                    // Validasi parsing berhasil
                                    if (!$cartStartTime || !$cartEndTime || !$existingStartTime || !$existingEndTime) {
                                        continue;
                                    }

                                    // Cek overlap waktu yang lebih akurat
                                    // Overlap terjadi jika: start1 < end2 AND start2 < end1
                                    if ($cartStartTime < $existingEndTime && $existingStartTime < $cartEndTime) {
                                        return [
                                            'isDuplicate' => true,
                                        ];
                                    }
                                } catch (\Exception $e) {
                                    // Log error untuk debugging
                                    Log::error('Error parsing time in duplicate check: ' . $e->getMessage(), [
                                        'cart_jam' => $cartJam,
                                        'existing_jam' => $existingJam
                                    ]);
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ['isDuplicate' => false];
    }

    private function checkAvailability($item, $userId)
    {
        // Query untuk mencari penyewaan dari user lain yang sudah ada dengan kondisi yang sama
        $existingBooking = Penyewaan::where('id_user', '!=', $userId) // User lain
            ->where('id_lokasi', $item['id_lokasi'])
            ->where('kategori_sewa', $item['kategori_sewa'])
            ->whereIn('status', ['Pending', 'Confirmed']) // Status yang masih aktif
            ->get();

        if ($item['kategori_sewa'] == 'per hari') {
            // Untuk penyewaan per hari, cek apakah ada tanggal yang tumpang tindih
            foreach ($existingBooking as $existing) {
                if (!empty($existing->penyewaan_per_hari)) {
                    // Ambil semua tanggal dari item keranjang
                    $cartDates = [];
                    foreach ($item['penyewaan_per_hari'] as $range) {
                        $start = Carbon::parse($range['tgl_mulai']);
                        $end = Carbon::parse($range['tgl_selesai']);

                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            $cartDates[] = $date->format('Y-m-d');
                        }
                    }

                    // Ambil semua tanggal dari booking yang sudah ada
                    $existingDates = [];
                    foreach ($existing->penyewaan_per_hari as $existingRange) {
                        $start = Carbon::parse($existingRange['tgl_mulai']);
                        $end = Carbon::parse($existingRange['tgl_selesai']);

                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            $existingDates[] = $date->format('Y-m-d');
                        }
                    }

                    // Cek apakah ada tanggal yang sama
                    $intersection = array_intersect($cartDates, $existingDates);
                    if (!empty($intersection)) {
                        return [
                            'isAvailable' => false,
                            'conflictDates' => $intersection,
                            'bookedBy' => $existing->id_user
                        ];
                    }
                }
            }
        } elseif ($item['kategori_sewa'] == 'per jam') {
            // Untuk penyewaan per jam, cek apakah ada waktu yang tumpang tindih
            foreach ($existingBooking as $existing) {
                if (!empty($existing->penyewaan_per_jam)) {
                    // Cek apakah ada konflik jam pada tanggal yang sama
                    foreach ($item['penyewaan_per_jam'] as $cartJam) {
                        // Pastikan key yang diperlukan ada
                        if (!isset($cartJam['tgl_mulai'], $cartJam['jam_mulai'], $cartJam['jam_selesai'])) {
                            continue; // Skip jika struktur data tidak sesuai
                        }

                        foreach ($existing->penyewaan_per_jam as $existingJam) {
                            // Pastikan key yang diperlukan ada di data existing
                            if (!isset($existingJam['tgl_mulai'], $existingJam['jam_mulai'], $existingJam['jam_selesai'])) {
                                continue; // Skip jika struktur data tidak sesuai
                            }

                            // Cek apakah tanggal sama
                            if ($cartJam['tgl_mulai'] === $existingJam['tgl_mulai']) {
                                try {
                                    // Parse waktu dengan lebih robust
                                    $cartStartTime = $this->parseTimeString($cartJam['jam_mulai']);
                                    $cartEndTime = $this->parseTimeString($cartJam['jam_selesai']);
                                    $existingStartTime = $this->parseTimeString($existingJam['jam_mulai']);
                                    $existingEndTime = $this->parseTimeString($existingJam['jam_selesai']);

                                    // Validasi parsing berhasil
                                    if (!$cartStartTime || !$cartEndTime || !$existingStartTime || !$existingEndTime) {
                                        continue;
                                    }

                                    // Cek overlap waktu yang lebih akurat
                                    // Overlap terjadi jika: start1 < end2 AND start2 < end1
                                    if ($cartStartTime < $existingEndTime && $existingStartTime < $cartEndTime) {
                                        return [
                                            'isAvailable' => false,
                                            'conflictDateTime' => [
                                                'date' => $cartJam['tgl_mulai'],
                                                'time' => $this->formatTimeForDisplay($cartJam['jam_mulai']) . '-' . $this->formatTimeForDisplay($cartJam['jam_selesai']),
                                                'existing_time' => $this->formatTimeForDisplay($existingJam['jam_mulai']) . '-' . $this->formatTimeForDisplay($existingJam['jam_selesai'])
                                            ],
                                            'bookedBy' => $existing->id_user
                                        ];
                                    }
                                } catch (\Exception $e) {
                                    // Log error untuk debugging
                                    Log::error('Error parsing time in availability check: ' . $e->getMessage(), [
                                        'cart_jam' => $cartJam,
                                        'existing_jam' => $existingJam
                                    ]);
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ['isAvailable' => true];
    }

    /**
     * Parse time string to minutes for comparison
     * Supports formats: HH:MM:SS, HH:MM, H:MM, HH.MM, H.MM
     */
    private function parseTimeString($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        // Clean the string and handle various formats
        $timeString = trim($timeString);

        // Replace dots with colons for consistency
        $timeString = str_replace('.', ':', $timeString);

        // Try to parse with Carbon - try multiple formats
        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];

        foreach ($formats as $format) {
            try {
                $time = Carbon::createFromFormat($format, $timeString);
                // Convert to minutes since midnight for easy comparison
                return $time->hour * 60 + $time->minute;
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try to extract numbers manually as fallback
        if (preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', $timeString, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return $hour * 60 + $minute;
            }
        }

        Log::error('Failed to parse time string: ' . $timeString);
        return null;
    }

    /**
     * Format time for display (remove seconds if present)
     */
    private function formatTimeForDisplay($timeString)
    {
        if (empty($timeString)) {
            return $timeString;
        }

        // Remove seconds from time display
        if (preg_match('/(\d{1,2}:\d{2}):\d{2}/', $timeString, $matches)) {
            return $matches[1];
        }

        return $timeString;
    }

    public function checkout()
    {
        // Cek apakah keranjang kosong
        if (empty($this->keranjangItems)) {
            session()->flash('error', 'Keranjang kosong. Tidak ada yang dapat di-checkout.');
            return;
        }

        // Cek apakah user sudah login
        if (!Auth::check()) {
            // Simpan URL saat ini ke session untuk redirect kembali setelah login
            Session::put('url.intended', route('keranjang'));

            session()->flash('error', 'Silakan login terlebih dahulu untuk melanjutkan checkout.');

            // Redirect ke halaman login
            return redirect()->route('login');
        }

        // Ambil data user termasuk NIK
        $user = User::find(Auth::id());
        $userId = Auth::id();

        // Validasi duplikasi untuk setiap item di keranjang (booking sendiri)
        $duplicateItems = [];
        foreach ($this->keranjangItems as $index => $item) {
            $duplicateCheck = $this->checkDuplicateBooking($item, $userId);

            if ($duplicateCheck['isDuplicate']) {
                $duplicateItems[] = [
                    'index' => $index,
                    'item' => $item,
                ];
            }
        }

        // Validasi ketersediaan untuk setiap item di keranjang (booking user lain)
        $unavailableItems = [];
        foreach ($this->keranjangItems as $index => $item) {
            $availabilityCheck = $this->checkAvailability($item, $userId);

            if (!$availabilityCheck['isAvailable']) {
                $unavailableItems[] = [
                    'index' => $index,
                    'item' => $item,
                    'availability' => $availabilityCheck
                ];
            }
        }

        // Jika ada item duplikat, tampilkan error dan hentikan proses checkout
        if (!empty($duplicateItems)) {
            $errorMessages = [];
            foreach ($duplicateItems as $duplicate) {
                $errorMessages[] = "• " . $duplicate['item']['nama_lokasi'];
            }

            $fullErrorMessage = "Pesanan yang sama sudah pernah Anda checkout sebelumnya. Silakan hapus item tersebut dari keranjang atau pilih waktu yang berbeda.";

            session()->flash('error', $fullErrorMessage);
            return;
        }

        // Jika ada item yang tidak tersedia, tampilkan error dan hentikan proses checkout
        if (!empty($unavailableItems)) {
            $errorMessages = [];
            foreach ($unavailableItems as $unavailable) {
                $item = $unavailable['item'];
                $availability = $unavailable['availability'];

                if ($item['kategori_sewa'] == 'per hari') {
                    $conflictDates = implode(', ', $availability['conflictDates']);
                    $errorMessages[] = "• " . $item['nama_lokasi'] . " (Per Hari) - Tanggal " . $conflictDates . " sudah dipesan user lain";
                } elseif ($item['kategori_sewa'] == 'per jam') {
                    $conflict = $availability['conflictDateTime'];
                    $errorMessages[] = "• " . $item['nama_lokasi'] . " (Per Jam) - " . $conflict['date'] . " jam " . $conflict['time'] . " sudah dipesan user lain";
                }
            }

            $fullErrorMessage = "Tempat tidak tersedia karena sudah dipesan user lain:\n\n" .
                implode("\n", $errorMessages) .
                "\n\nSilakan hapus item tersebut dari keranjang atau pilih waktu yang berbeda.";

            session()->flash('error', $fullErrorMessage);
            return;
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            $createdTransactions = [];

            // Simpan semua item dari keranjang ke database
            foreach ($this->keranjangItems as $item) {
                // Siapkan data penyewaan
                $penyewaanData = [
                    'id_user' => $userId,
                    'nik' => $user->nik,
                    'id_lokasi' => $item['id_lokasi'],
                    'kategori_sewa' => $item['kategori_sewa'],
                    'tgl_booking' => $item['tgl_booking'],
                    'deskripsi' => $item['deskripsi'],
                    'total_durasi' => $item['total_durasi'],
                    'tarif' => $item['tarif'],
                    'sub_total' => $item['sub_total'],
                    'status' => 'Pending'
                ];

                // Tambahkan data penyewaan per hari atau per jam sesuai kategori
                if ($item['kategori_sewa'] == 'per hari') {
                    $penyewaanData['penyewaan_per_hari'] = $item['penyewaan_per_hari'];
                    $penyewaanData['penyewaan_per_jam'] = null; // Pastikan field lain null
                } elseif ($item['kategori_sewa'] == 'per jam') {
                    $penyewaanData['penyewaan_per_jam'] = $item['penyewaan_per_jam'];
                    $penyewaanData['penyewaan_per_hari'] = null; // Pastikan field lain null
                }

                // Buat record penyewaan
                $penyewaan = Penyewaan::create($penyewaanData);
                $expiredAt = now()->addHours(2);
                // Siapkan data transaksi
                $detailPenyewaan = $this->prepareDetailPenyewaan($item);

                $transaksiData = [
                    'id_penyewaan' => $penyewaan->id_penyewaan,
                    'nik' => $user->nik,
                    'tgl_booking' => $item['tgl_booking'],
                    'detail_penyewaan' => $detailPenyewaan,
                    'total_durasi' => $item['total_durasi'],
                    'luas' => $item['luas'] ?? null,
                    'tarif' => $item['tarif'],
                    'sub_total' => $item['sub_total'],
                    'status' => 'Pending',
                    'expired_at' => $expiredAt
                ];

                // Buat record transaksi
                $transaksi = Transaksi::create($transaksiData);
                $createdTransactions[] = $transaksi;

                Log::info('Transaction created successfully', [
                    'penyewaan_id' => $penyewaan->id_penyewaan,
                    'user_id' => $userId
                ]);
            }

            // Commit transaction
            DB::commit();

            // Kosongkan keranjang setelah checkout berhasil
            Session::forget('keranjang');

            // Refresh data komponen
            $this->keranjangItems = [];
            $this->totalKeseluruhan = 0;

            // Perbarui jumlah item di keranjang (untuk badge notifikasi)
            $this->dispatch('keranjangUpdated', count: 0);

            session()->flash('message', 'Checkout berhasil! Semua penyewaan dan transaksi telah diproses. Silakan lanjutkan pembayaran sebelum ' . $expiredAt->format('d/m/Y H:i') . '.');

            return redirect()->route('cetak');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'cart_items' => $this->keranjangItems,
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.keranjang');
    }
}

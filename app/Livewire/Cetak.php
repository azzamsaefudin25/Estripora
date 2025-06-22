<?php


namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Penyewaan;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Cetak extends Component
{
    use WithFileUploads;

    public string $idBilling = '';
    public $transaksis = [];
    public $selectedTransaksis = [];
    public $metodePembayaran = '';
    public $selectAll = false;
    
    // Properties untuk upload bukti bayar
    public $idBillingUpload = '';
    public $buktiBayar;
    public $isUploading = false;

    // Properties untuk countdown timer
    public $pendingTransaksis = [];

    protected $rules = [
        'metodePembayaran' => 'required|in:ATM,Mobile Banking,Teller Bank',
        'idBillingUpload' => 'required|string',
        'buktiBayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    protected $messages = [
        'idBillingUpload.required' => 'ID Billing harus diisi.',
        'buktiBayar.required' => 'File bukti bayar harus diupload.',
        'buktiBayar.mimes' => 'File harus berformat JPG, JPEG, PNG, atau PDF.',
        'buktiBayar.max' => 'Ukuran file maksimal 5MB.',
    ];

    public function mount()
    {
        $this->cleanupExpiredTransactions(); // Cleanup saat load page
        $this->loadTransaksis();
    }

    public function generateBilling()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk membuat billing.');
            return;
        }

        $this->validate(['metodePembayaran' => 'required|in:ATM,Mobile Banking,Teller Bank']);

        if (!$this->metodePembayaran) {
            session()->flash('error', 'Metode pembayaran belum dipilih.');
            return;
        }

        $user = Auth::user();
        $userId = $user->id;

        $penyewaan = Penyewaan::where('id_user', $userId)
            ->where('status', 'Pending')
            ->doesntHave('transaksi')
            ->latest('created_at')
            ->first();

        if (!$penyewaan) {
            session()->flash('error', 'Tidak ada penyewaan yang bisa diproses.');
            return;
        }

        // Cek apakah sudah ada transaksi Pending untuk penyewaan ini
        $existing = Transaksi::where('id_penyewaan', $penyewaan->id_penyewaan)
            ->where('status', 'Pending')
            ->where('expired_at', '>', Carbon::now()) // Pastikan belum expired
            ->first();

        if ($existing) {
            session()->flash('error', 'Kamu sudah punya billing yang belum dibayar dan masih aktif.');
            return;
        }

        $this->idBilling = 'BILL-' . strtoupper(Str::random(8));

        $perHari = $penyewaan->penyewaan_per_hari ?? [];
        $perJam = $penyewaan->penyewaan_per_jam ?? [];

        // Set expired time: 2 jam dari sekarang
        $expiredAt = Carbon::now()->addHours(2);

        Transaksi::create([
            'id_penyewaan'      => $penyewaan->id_penyewaan,
            'id_billing'        => $this->idBilling,
            'nik'               => $penyewaan->nik,
            'tgl_booking'       => $penyewaan->tgl_booking,
            'detail_penyewaan'  => json_encode([
                'tipe'    => $penyewaan->kategori_sewa,
                'per_hari'=> $perHari,
                'per_jam' => $perJam,
            ]),
            'total_durasi'      => $penyewaan->total_durasi,
            'luas'              => null,
            'tarif'             => $penyewaan->tarif,
            'sub_total'         => $penyewaan->sub_total,
            'status'            => 'Pending',
            'metode_pembayaran' => $this->metodePembayaran,
            'expired_at'        => $expiredAt, // TAMBAHAN: Set waktu kadaluarsa
        ]);

        session()->flash('success', 'Transaksi berhasil dibuat. Silakan lakukan pembayaran dalam 2 jam sebelum kadaluarsa.');
        $this->loadTransaksis();
    }

    public function uploadBuktiBayar()
    {
        $this->isUploading = true;
        
        try {
            $this->validate([
                'idBillingUpload' => 'required|string',
                'buktiBayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $userId = Auth::id();

            // Cari transaksi berdasarkan ID Billing dan pastikan milik user yang login
            $transaksi = Transaksi::where('id_billing', $this->idBillingUpload)
                ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
                ->first();

            if (!$transaksi) {
                session()->flash('error', 'ID Billing tidak ditemukan atau tidak valid.');
                $this->isUploading = false;
                return;
            }

            // TAMBAHAN: Cek apakah transaksi sudah expired
            if ($transaksi->expired_at && Carbon::now()->greaterThan($transaksi->expired_at)) {
                session()->flash('error', 'Transaksi ini sudah kadaluarsa. Silakan buat billing baru.');
                $this->isUploading = false;
                return;
            }

            if ($transaksi->status !== 'Pending') {
                session()->flash('error', 'Transaksi ini sudah diproses dan tidak dapat diubah.');
                $this->isUploading = false;
                return;
            }

            // Hapus file lama jika ada
            if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                Storage::disk('public')->delete($transaksi->bukti_bayar);
            }

            // Upload file baru
            $filename = 'bukti_bayar_' . $transaksi->id_billing . '_' . time() . '.' . $this->buktiBayar->getClientOriginalExtension();
            $path = $this->buktiBayar->storeAs('bukti_bayar', $filename, 'public');

            // Update: Simpan path file ke database
            $transaksi->update([
                'bukti_bayar' => $path
            ]);

            session()->flash('success', 'Bukti bayar berhasil diupload.');
            
            // Reset form
            $this->reset(['idBillingUpload', 'buktiBayar']);
            $this->loadTransaksis();

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupload bukti bayar: ' . $e->getMessage());
        }

        $this->isUploading = false;
    }

    public function loadTransaksis()
    {
        $userId = Auth::id();
        $this->transaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->latest()
            ->get();

        // Load pending transactions untuk countdown timer
        $this->pendingTransaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->where('status', 'Pending')
            ->where('expired_at', '>', Carbon::now())
            ->get()
            ->map(function ($transaksi) {
                return [
                    'id' => $transaksi->id,
                    'id_billing' => $transaksi->id_billing,
                    'expired_at' => $transaksi->expired_at->toISOString(),
                    'remaining_seconds' => Carbon::now()->diffInSeconds($transaksi->expired_at, false)
                ];
            })
            ->toArray();
    }

    public function cleanupExpiredTransactions()
    {
        $userId = Auth::id();
        
        $expiredTransaksis = Transaksi::whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereIn('status', ['Pending', 'Failed'])
            ->where('expired_at', '<', Carbon::now())
            ->get();

        foreach ($expiredTransaksis as $transaksi) {
            // Hapus file bukti bayar jika ada
            if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                Storage::disk('public')->delete($transaksi->bukti_bayar);
            }
            
            $transaksi->delete();
        }

        if ($expiredTransaksis->count() > 0) {
            session()->flash('error', $expiredTransaksis->count() . ' transaksi yang kadaluarsa telah dihapus otomatis.');
        }
    }

    public function hapusTransaksi($id)
    {
        $userId = Auth::id();

        $transaksi = Transaksi::find($id);

        // Pastikan hanya bisa hapus transaksi miliknya
        if ($transaksi && $transaksi->penyewaan->id_user == $userId) {
            if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                Storage::disk('public')->delete($transaksi->bukti_bayar);
            }
            
            $transaksi->delete();
            session()->flash('success', 'Transaksi berhasil dihapus.');
        } else {
            session()->flash('error', 'Transaksi tidak ditemukan atau tidak berhak menghapus.');
        }

        $this->loadTransaksis();
        $this->selectedTransaksis = array_filter($this->selectedTransaksis, fn($val) => $val != $id);
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedTransaksis = collect($this->transaksis)->pluck('id')->toArray();
        } else {
            $this->selectedTransaksis = [];
        }
    }

    public function updatedSelectedTransaksis()
    {
        $transaksiCollection = collect($this->transaksis);
        $this->selectAll = count($this->selectedTransaksis) === $transaksiCollection->count() && $transaksiCollection->count() > 0;
    }

public function cetakPDF()
{
    if (empty($this->selectedTransaksis)) {
        session()->flash('error', 'Pilih transaksi yang akan diprint!');
        return;
    }

    $userId = Auth::id();

    $transaksis = Transaksi::with([
        'penyewaan.lokasi',
        'penyewaan.lokasi.tempat', // Eksplisit load tempat
    ])
        ->whereIn('id', $this->selectedTransaksis)
        ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
        ->get();
    if ($transaksis->isEmpty()) {
        session()->flash('error', 'Transaksi tidak ditemukan atau tidak berhak mengakses.');
        return;
    }

    try {
        foreach ($transaksis as $transaksi) {
            $pdf = Pdf::loadView('pdf.validasi-penyewaan', compact('transaksi'));
            $pdf->setPaper('A4', 'portrait');
        }
        
        $filename = 'validasi_penyewaan_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);

    } catch (\Exception $e) {
        session()->flash('error', 'Gagal membuat PDF: ' . $e->getMessage());
        return;
    }
}

    public function render()
    {
        return view('livewire.cetak', [
            'transaksis' => $this->transaksis,
            'pendingTransaksis' => $this->pendingTransaksis,
        ]);
    }
}
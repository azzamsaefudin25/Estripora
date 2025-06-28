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
use Illuminate\Support\Facades\Log;


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
        $this->loadAvailableBillings();
        $this->cleanupExpiredTransactions();
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

        // Cari transaksi yang belum memiliki id_billing (dibuat saat checkout)
        $transaksi = Transaksi::whereHas('penyewaan', function($query) use ($userId) {
                $query->where('id_user', $userId);
            })
            ->where('status', 'Pending')
            ->whereNull('id_billing')
            ->latest('created_at')
            ->first();

        if (!$transaksi) {
            session()->flash('error', 'Tidak ada transaksi yang bisa diproses. Pastikan Anda sudah melakukan checkout.');
            return;
        }

        // Cek apakah sudah ada transaksi dengan id_billing yang aktif untuk user ini
        $existingActiveBilling = Transaksi::whereHas('penyewaan', function($query) use ($userId) {
                $query->where('id_user', $userId);
            })
            ->where('status', 'Pending')
            ->whereNotNull('id_billing') // Sudah punya id_billing
            ->where(function($query) {
                $query->whereNull('expired_at')
                      ->orWhere('expired_at', '>', Carbon::now());
            })
            ->first();

        if ($existingActiveBilling) {
            session()->flash('error', 'Kamu sudah punya billing yang belum dibayar dan masih aktif.');
            return;
        }

        // Generate ID Billing baru
        $this->idBilling = 'BILL-' . strtoupper(Str::random(8));

        // Set expired time: 2 jam dari sekarang
        $expiredAt = Carbon::now()->addHours(2);

        // Update transaksi yang sudah ada dengan id_billing dan metode pembayaran
        $transaksi->update([
            'id_billing'        => $this->idBilling,
            'metode_pembayaran' => $this->metodePembayaran,
            'expired_at'        => $expiredAt,
        ]);

        session()->flash('success', 'Billing berhasil dibuat. Silakan lakukan pembayaran dalam 2 jam sebelum kadaluarsa.');
        $this->loadAvailableBillings();
        $this->loadTransaksis();
    }

    public $availableBillings = [];
    public $selectedBillingDetail = null;

    public function loadAvailableBillings()
    {
        $userId = Auth::id();
        
        $this->availableBillings = Transaksi::where('status', 'Pending')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereNotNull('id_billing') // Hanya transaksi yang sudah punya id_billing
            ->where(function($query) {
                $query->whereNull('expired_at')
                      ->orWhere('expired_at', '>', Carbon::now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatedIdBillingUpload($value)
    {
        if ($value) {
            $this->selectedBillingDetail = collect($this->availableBillings)
                ->firstWhere('id_billing', $value);
        } else {
            $this->selectedBillingDetail = null;
        }
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

            $transaksi = Transaksi::where('id_billing', $this->idBillingUpload)
                ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
                ->first();

            if (!$transaksi) {
                session()->flash('error', 'ID Billing tidak ditemukan atau tidak valid.');
                $this->isUploading = false;
                return;
            }

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

            if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                Storage::disk('public')->delete($transaksi->bukti_bayar);
            }

            $filename = 'bukti_bayar_' . $transaksi->id_billing . '_' . time() . '.' . $this->buktiBayar->getClientOriginalExtension();
            $path = $this->buktiBayar->storeAs('bukti_bayar', $filename, 'public');

            $transaksi->update([
                'bukti_bayar' => $path
            ]);

            session()->flash('success', 'Bukti bayar berhasil diupload.');
            
            $this->reset(['idBillingUpload', 'buktiBayar']);
            $this->selectedBillingDetail = null;
            $this->loadAvailableBillings();
            $this->loadTransaksis();

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupload bukti bayar: ' . $e->getMessage());
        }

        $this->isUploading = false;
    }

    public function refreshData()
    {
        $this->loadAvailableBillings();
        $this->loadTransaksis();
    }

    public function loadTransaksis()
    {
        $userId = Auth::id();
        $this->transaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->latest()
            ->get();

        $this->pendingTransaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->where('status', 'Pending')
            ->whereNotNull('id_billing') // Hanya yang sudah punya id_billing
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
        
        // Cleanup transaksi yang sudah expired dan punya id_billing
        $expiredTransaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereIn('status', ['Pending', 'Failed'])
            ->whereNotNull('id_billing')
            ->where('expired_at', '<', Carbon::now())
            ->get();

        $deletedCount = 0;

        foreach ($expiredTransaksis as $transaksi) {
            try {
                // Hapus file bukti bayar jika ada
                if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                    Storage::disk('public')->delete($transaksi->bukti_bayar);
                }
                
                // Simpan data penyewaan untuk dihapus
                $penyewaan = $transaksi->penyewaan;
                
                // Hapus transaksi terlebih dahulu (karena foreign key constraint)
                $transaksi->delete();
                
                // Hapus penyewaan jika ada
                if ($penyewaan) {
                    $penyewaan->delete();
                }
                
                $deletedCount++;
                
            } catch (\Exception $e) {
                // Log error jika diperlukan, tapi lanjutkan proses cleanup
                Log::error('Failed to cleanup expired transaction: ' . $e->getMessage(), [
                    'transaksi_id' => $transaksi->id,
                    'user_id' => $userId
                ]);
            }
        }

        if ($deletedCount > 0) {
            session()->flash('info', $deletedCount . ' transaksi dan penyewaan yang kadaluarsa telah dihapus otomatis.');
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

    public function cetakPDFSingle($transaksiId)
    {
        $userId = Auth::id();

        $transaksi = Transaksi::with([
            'penyewaan.lokasi',
            'penyewaan.lokasi.tempat', // Eksplisit load tempat
        ])
            ->where('id', $transaksiId)
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->first();

        if (!$transaksi) {
            session()->flash('error', 'Transaksi tidak ditemukan atau tidak berhak mengakses.');
            return;
        }

        try {
            $pdf = Pdf::loadView('pdf.validasi-penyewaan', compact('transaksi'));
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'validasi_penyewaan_' . $transaksi->id_billing . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
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
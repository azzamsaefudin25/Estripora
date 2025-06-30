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
use Illuminate\Support\Facades\DB;

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
    
    // Properties untuk checkout sessions
    public $availableCheckoutSessions = [];
    public $selectedCheckoutSession = '';

    protected $rules = [
        'metodePembayaran' => 'required|in:ATM,Mobile Banking,Teller Bank',
        'selectedCheckoutSession' => 'required|string',
        'idBillingUpload' => 'required|string',
        'buktiBayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    protected $messages = [
        'selectedCheckoutSession.required' => 'Checkout session harus dipilih.',
        'idBillingUpload.required' => 'ID Billing harus diisi.',
        'buktiBayar.required' => 'File bukti bayar harus diupload.',
        'buktiBayar.mimes' => 'File harus berformat JPG, JPEG, PNG, atau PDF.',
        'buktiBayar.max' => 'Ukuran file maksimal 5MB.',
    ];

    public function mount()
    {
        $this->loadAvailableCheckoutSessions();
        $this->loadAvailableBillings();
        $this->cleanupExpiredTransactions();
        $this->loadTransaksis();
    }

    public function loadAvailableCheckoutSessions()
    {
        $userId = Auth::id();
        
        // Ambil checkout sessions yang belum memiliki id_billing
$this->availableCheckoutSessions = Transaksi::where('status', 'Pending')
    ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
    ->whereNull('id_billing')
    ->whereNotNull('checkout_session')
    ->select(
        'checkout_session',
        DB::raw('COUNT(*) as item_count'),
        DB::raw('SUM(sub_total) as total_amount'),
        DB::raw('MAX(created_at) as latest_created_at') // Tambahan ini
    )
    ->groupBy('checkout_session')
    ->orderByDesc('latest_created_at') // Ganti dari created_at
    ->get()
    ->map(function ($item) {
        $firstTransaction = Transaksi::where('checkout_session', $item->checkout_session)->first();
        $item->created_at = $firstTransaction->created_at;
        $item->expired_at = $firstTransaction->expired_at;
        return $item;
    });

    }

public function generateBilling()
{
    if (!Auth::check()) {
        session()->flash('error', 'Silakan login terlebih dahulu untuk membuat billing.');
        return;
    }

    $this->validate([
        'metodePembayaran' => 'required|in:ATM,Mobile Banking,Teller Bank'
    ]);

    $userId = Auth::id();

    // Cek apakah sudah ada billing aktif
    $existingActiveBilling = Transaksi::whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
        ->where('status', 'Pending')
        ->whereNotNull('id_billing')
        ->where(function ($query) {
            $query->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
        })
        ->first();

    if ($existingActiveBilling) {
        session()->flash('error', 'Kamu sudah memiliki billing yang aktif. Selesaikan dulu sebelum membuat yang baru.');
        return;
    }

    // Ambil 1 checkout session aktif (tanpa id_billing)
    $checkoutSession = Transaksi::whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
        ->where('status', 'Pending')
        ->whereNull('id_billing')
        ->whereNotNull('checkout_session')
        ->orderByDesc('created_at')
        ->value('checkout_session');

    if (!$checkoutSession) {
        session()->flash('error', 'Tidak ditemukan checkout session yang bisa diproses.');
        return;
    }

    $transaksis = Transaksi::where('checkout_session', $checkoutSession)
        ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
        ->where('status', 'Pending')
        ->whereNull('id_billing')
        ->get();

    if ($transaksis->isEmpty()) {
        session()->flash('error', 'Checkout session ini tidak memiliki transaksi yang bisa dibilling.');
        return;
    }

    DB::beginTransaction();

    try {
        $this->idBilling = 'BILL-' . strtoupper(Str::random(8));
        $expiredAt = now()->addHours(2);

        foreach ($transaksis as $transaksi) {
            $transaksi->update([
                'id_billing'        => $this->idBilling,
                'metode_pembayaran' => $this->metodePembayaran,
                'expired_at'        => $expiredAt,
            ]);
        }

        DB::commit();

        session()->flash('success', "Billing berhasil dibuat untuk " . count($transaksis) . " transaksi.");

        $this->loadAvailableCheckoutSessions();
        $this->loadAvailableBillings();
        $this->loadTransaksis();

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Generate billing failed: ' . $e->getMessage(), [
            'user_id' => $userId,
            'trace' => $e->getTraceAsString()
        ]);

        session()->flash('error', 'Terjadi kesalahan saat membuat billing.');
    }
}


    public $availableBillings = [];
    public $selectedBillingDetail = null;

    public function loadAvailableBillings()
    {
        $userId = Auth::id();
        
        // Load billing berdasarkan id_billing, bukan per transaksi individual
        $this->availableBillings = Transaksi::where('status', 'Pending')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereNotNull('id_billing')
            ->where(function($query) {
                $query->whereNull('expired_at')
                      ->orWhere('expired_at', '>', Carbon::now());
            })
            ->select('id_billing', 'metode_pembayaran', 'expired_at', 'checkout_session', 
                     DB::raw('COUNT(*) as item_count'), 
                     DB::raw('SUM(sub_total) as total_amount'),
                     DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('id_billing', 'metode_pembayaran', 'expired_at', 'checkout_session')
            ->orderBy('latest_created_at', 'desc')
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

            // Ambil semua transaksi dengan id_billing yang sama
            $transaksis = Transaksi::where('id_billing', $this->idBillingUpload)
                ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
                ->get();

            if ($transaksis->isEmpty()) {
                session()->flash('error', 'ID Billing tidak ditemukan atau tidak valid.');
                $this->isUploading = false;
                return;
            }

            $firstTransaksi = $transaksis->first();

            if ($firstTransaksi->expired_at && Carbon::now()->greaterThan($firstTransaksi->expired_at)) {
                session()->flash('error', 'Transaksi ini sudah kadaluarsa. Silakan buat billing baru.');
                $this->isUploading = false;
                return;
            }

            if ($firstTransaksi->status !== 'Pending') {
                session()->flash('error', 'Transaksi ini sudah diproses dan tidak dapat diubah.');
                $this->isUploading = false;
                return;
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Hapus file bukti bayar lama jika ada
                foreach ($transaksis as $transaksi) {
                    if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                        Storage::disk('public')->delete($transaksi->bukti_bayar);
                    }
                }

                // Upload file baru
                $filename = 'bukti_bayar_' . $this->idBillingUpload . '_' . time() . '.' . $this->buktiBayar->getClientOriginalExtension();
                $path = $this->buktiBayar->storeAs('bukti_bayar', $filename, 'public');

                // Update semua transaksi dengan bukti bayar yang sama
                foreach ($transaksis as $transaksi) {
                    $transaksi->update([
                        'bukti_bayar' => $path
                    ]);
                }

                // Commit transaction
                DB::commit();

                session()->flash('success', 'Bukti bayar berhasil diupload untuk ' . count($transaksis) . ' transaksi.');
                
                $this->reset(['idBillingUpload', 'buktiBayar']);
                $this->selectedBillingDetail = null;
                $this->loadAvailableBillings();
                $this->loadTransaksis();

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupload bukti bayar: ' . $e->getMessage());
        }

        $this->isUploading = false;
    }

    public function refreshData()
    {
        $this->loadAvailableCheckoutSessions();
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
            ->whereNotNull('id_billing')
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
        
        // Cleanup transaksi yang sudah expired dan punya checkout_session
        $expiredTransaksis = Transaksi::with('penyewaan')
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereIn('status', ['Pending', 'Failed'])
            ->whereNotNull('checkout_session')
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

    public function hapusCheckoutSession($checkoutSession)
    {
        $userId = Auth::id();

        // Ambil semua transaksi dalam checkout session
        $transaksis = Transaksi::whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->where('checkout_session', $checkoutSession)
            ->where('status', 'Pending')
            ->whereNull('id_billing') // Hanya yang belum punya billing
            ->get();

        if ($transaksis->isEmpty()) {
            session()->flash('error', 'Checkout session tidak ditemukan atau sudah diproses.');
            return;
        }

        DB::beginTransaction();

        try {
            $deletedCount = 0;
            
            foreach ($transaksis as $transaksi) {
                // Hapus file bukti bayar jika ada
                if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                    Storage::disk('public')->delete($transaksi->bukti_bayar);
                }
                
                // Simpan data penyewaan untuk dihapus
                $penyewaan = $transaksi->penyewaan;
                
                // Hapus transaksi terlebih dahulu
                $transaksi->delete();
                
                // Hapus penyewaan jika ada
                if ($penyewaan) {
                    $penyewaan->delete();
                }
                
                $deletedCount++;
            }

            DB::commit();

            session()->flash('success', "Berhasil menghapus {$deletedCount} transaksi dari checkout session.");
            
            $this->loadAvailableCheckoutSessions();
            $this->loadTransaksis();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete checkout session: ' . $e->getMessage(), [
                'checkout_session' => $checkoutSession,
                'user_id' => $userId
            ]);
            
            session()->flash('error', 'Gagal menghapus checkout session: ' . $e->getMessage());
        }
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
            'penyewaan.lokasi.tempat',
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

    public function cetakPDFBilling($idBilling)
    {
        $userId = Auth::id();

        // Ambil semua transaksi dengan id_billing yang sama
        $transaksis = Transaksi::with([
            'penyewaan.lokasi',
            'penyewaan.lokasi.tempat',
        ])
            ->where('id_billing', $idBilling)
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->get();

        if ($transaksis->isEmpty()) {
            session()->flash('error', 'Transaksi tidak ditemukan atau tidak berhak mengakses.');
            return;
        }

        try {
            $pdf = Pdf::loadView('pdf.validasi-penyewaan-billing', compact('transaksis'));
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'validasi_penyewaan_billing_' . $idBilling . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
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
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTransactions extends Command
{
    protected $signature = 'transaksi:cleanup-expired';
    protected $description = 'Hapus transaksi dan penyewaan yang sudah kadaluarsa (lebih dari 2 jam)';

    public function handle()
    {
        $expiredTransaksis = Transaksi::with('penyewaan')
            ->whereIn('status', ['Pending', 'Failed'])
            ->whereNotNull('id_billing') // Hanya yang sudah punya id_billing
            ->where('expired_at', '<', Carbon::now())
            ->get();
            
        if ($expiredTransaksis->isEmpty()) {
            $this->info("Tidak ada transaksi yang expired.");
            return 0;
        }

        $this->info("Ditemukan {$expiredTransaksis->count()} transaksi yang expired. Memulai proses cleanup...");

        $deletedTransaksiCount = 0;
        $deletedPenyewaanCount = 0;
        $errorCount = 0;

        foreach ($expiredTransaksis as $transaksi) {
            try {
                $this->info("Processing transaksi ID {$transaksi->id} (Billing: {$transaksi->id_billing}) - Status: {$transaksi->status}, Expired: {$transaksi->expired_at}");

                // Gunakan database transaction untuk memastikan konsistensi data
                DB::beginTransaction();

                // Hapus file bukti bayar jika ada
                if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                    Storage::disk('public')->delete($transaksi->bukti_bayar);
                    $this->info("  - Bukti bayar deleted: {$transaksi->bukti_bayar}");
                }

                // Simpan referensi penyewaan sebelum menghapus transaksi
                $penyewaan = $transaksi->penyewaan;
                $penyewaanId = $penyewaan ? $penyewaan->id_penyewaan : null;

                // Hapus transaksi terlebih dahulu (karena foreign key constraint)
                $transaksi->delete();
                $deletedTransaksiCount++;
                $this->info("  - Transaksi deleted: ID {$transaksi->id}");

                // Hapus penyewaan jika ada
                if ($penyewaan) {
                    $penyewaan->delete();
                    $deletedPenyewaanCount++;
                    $this->info("  - Penyewaan deleted: ID {$penyewaanId}");
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                $this->error("  - Error deleting transaksi ID {$transaksi->id}: " . $e->getMessage());
                
                // Log error untuk debugging
                Log::error('Failed to cleanup expired transaction via command', [
                    'transaksi_id' => $transaksi->id,
                    'id_billing' => $transaksi->id_billing ?? 'null',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Summary report
        $this->info("\n=== Cleanup Summary ===");
        $this->info("Transaksi deleted: {$deletedTransaksiCount}");
        $this->info("Penyewaan deleted: {$deletedPenyewaanCount}");
        
        if ($errorCount > 0) {
            $this->warn("Errors encountered: {$errorCount}");
            $this->warn("Check logs for detailed error information.");
        }

        $this->info("Cleanup process completed successfully.");
        return 0;
    }
}
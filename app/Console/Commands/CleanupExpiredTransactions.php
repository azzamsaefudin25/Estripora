<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupExpiredTransactions extends Command
{
    protected $signature = 'transaksi:cleanup-expired';
    protected $description = 'Hapus transaksi yang sudah kadaluarsa (lebih dari 2 jam)';

    public function handle()
    {
        $expiredTransaksis = Transaksi::where('status', 'Pending')
            ->where('expired_at', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredTransaksis as $transaksi) {
            // Hapus file bukti bayar jika ada
            if ($transaksi->bukti_bayar && Storage::disk('public')->exists($transaksi->bukti_bayar)) {
                Storage::disk('public')->delete($transaksi->bukti_bayar);
            }
            
            $transaksi->delete();
            $count++;
        }

        $this->info("Berhasil menghapus {$count} transaksi yang kadaluarsa.");
        return 0;
    }
}


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
        $expiredTransaksis = Transaksi::whereIn('status', ['Pending','Failed'])
            ->where('expired_at', '<', Carbon::now())
            ->get();
            
    if ($expiredTransaksis->isEmpty()) {
        $this->info("Tidak ada transaksi yang expired.");
        return 0;
    }

        $count = 0;
        foreach ($expiredTransaksis as $transaksi) {
            // Hapus file bukti bayar jika ada
            $this->info("Cek: akan hapus transaksi ID {$transaksi->id} status {$transaksi->status}, expired_at: {$transaksi->expired_at}");

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


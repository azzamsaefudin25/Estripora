<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Tambahkan enum baru + lama untuk menghindari error
        DB::statement("ALTER TABLE transaksi MODIFY metode_pembayaran ENUM('ATM', 'Mobile Banking', 'Teller Bank', 'Transfer Bank', 'E-Wallet', 'Kartu Kredit') NULL");

        // Step 2: Update data lama ke nilai baru
        DB::table('transaksi')->where('metode_pembayaran', 'Transfer Bank')->update(['metode_pembayaran' => 'ATM']);
        DB::table('transaksi')->where('metode_pembayaran', 'E-Wallet')->update(['metode_pembayaran' => 'Mobile Banking']);
        DB::table('transaksi')->where('metode_pembayaran', 'Kartu Kredit')->update(['metode_pembayaran' => 'Teller Bank']);

        // Step 3: Ubah enum agar hanya berisi nilai baru
        DB::statement("ALTER TABLE transaksi MODIFY metode_pembayaran ENUM('ATM', 'Mobile Banking', 'Teller Bank') NULL");

        // Kolom tambahan (jika ada)
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('bukti_bayar')->nullable()->after('metode_pembayaran');
            $table->timestamp('expired_at')->nullable()->after('bukti_bayar');
            $table->timestamp('reviewed_at')->nullable()->after('expired_at');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transaksi MODIFY metode_pembayaran ENUM('Transfer Bank', 'E-Wallet', 'Kartu Kredit') NULL");

        Schema::table('transaksi', function ($table) {
            $table->dropColumn('bukti_bayar');
            $table->dropColumn('expired_at');
            $table->dropColumn('reviewed_at');
        });
    }
};

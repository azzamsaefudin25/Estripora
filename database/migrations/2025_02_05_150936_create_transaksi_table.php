<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_penyewaan');
            $table->foreign('id_penyewaan')->references('id_penyewaan')->on('penyewaan')->onDelete('cascade');
            $table->string('id_billing')->nullable();
            $table->string('nik');
            $table->date('tgl_booking');
            $table->json('detail_penyewaan');
            $table->integer('total_durasi');
            $table->integer('luas')->nullable();
            $table->decimal('tarif', 16, 2);
            $table->decimal('sub_total', 16, 2);
            $table->enum('metode_pembayaran', ['ATM', 'Mobile Banking', 'Teller Bank'])->nullable();
            $table->enum('status', ['Pending', 'Paid', 'Failed'])->default('Pending');
            $table->string('bukti_bayar')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
};
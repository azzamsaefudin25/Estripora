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
            $table->string('id_billing');
            $table->date('tgl_booking');
            $table->json('detail_penyewaan');
            $table->integer('total_durasi');
            $table->integer('luas')->nullable();
            $table->decimal('tarif', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->enum('metode_pembayaran', ['Transfer Bank', 'E-Wallet', 'Kartu Kredit'])->nullable();
            $table->enum('status', ['Pending', 'Paid', 'Failed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};

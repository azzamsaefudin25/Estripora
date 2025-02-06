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
        Schema::create('penyewaan', function (Blueprint $table) {
            $table->integer('id_penyewaan')->primary();
            $table->string('nik');
            $table->foreign('nik')->references('nik')->on('customer')->onDelete('cascade');
            $table->integer('id_lokasi');
            $table->foreign('id_lokasi')->references('id_lokasi')->on('lokasi')->onDelete('cascade');
            $table->date('tgl_booking');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->text('deskripsi')->nullable();
            $table->integer('jumlah');
            $table->decimal('tarif', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->enum('status', ['Pending', 'Confirmed', 'Canceled'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaan');
    }
};

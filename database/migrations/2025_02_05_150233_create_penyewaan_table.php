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
            $table->foreignId('id_tempat')->constrained('tempat')->onDelete('cascade');
            $table->date('tgl_booking');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->integer('jumlah');
            $table->integer('tarif');
            $table->string('status');
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

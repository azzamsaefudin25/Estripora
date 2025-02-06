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
        Schema::create('lokasi', function (Blueprint $table) {
            $table->Integer('id_lokasi')->primary();
            $table->foreignId('id_tempat')->constrained('tempat')->onDelete('cascade');
            $table->string('nama_lokasi');
            $table->time('waktu')->nullable();
            $table->date('tanggal')->nullable();
            $table->decimal('tarif', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi');
    }
};

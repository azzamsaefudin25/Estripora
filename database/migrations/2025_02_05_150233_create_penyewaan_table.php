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
            $table->bigIncrements('id_penyewaan');
            $table->string('nik');
            $table->foreign('nik')->references('nik')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_lokasi');
            $table->foreign('id_lokasi')->references('id_lokasi')->on('lokasi')->onDelete('cascade');
            $table->enum('kategori_sewa', ['per jam', 'per hari']);
            $table->date('tgl_booking')->default(now());
            $table->json('jadwal_per_jam')->nullable();
            $table->json('jadwal_per_hari')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('total_durasi');
            $table->decimal('tarif', 10, 2);
            $table->decimal('sub_total', 10, 2)->default(0);
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

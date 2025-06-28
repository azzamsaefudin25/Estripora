<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lapor', function (Blueprint $table) {
            $table->id('id_lapor');
            $table->string('email');
            $table->unsignedBigInteger('id_penyewaan'); // Relasi dengan tabel Penyewaan
            $table->text('keluhan'); // Keluhan pengguna
            $table->string('foto')->nullable(); // Untuk menyimpan path foto
            $table->string('foto2')->nullable();
            $table->string('foto3')->nullable();
            $table->text('balasan')->nullable();
            $table->boolean('balasan_dilihat')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lapor');
    }
};

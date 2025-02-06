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
        Schema::create('ulasan', function (Blueprint $table) {
            $table->bigIncrements('id_ulasan');
            $table->unsignedBigInteger('id_penyewaan');
            $table->foreign('id_penyewaan')->references('id_penyewaan')->on('penyewaan')->onDelete('cascade');
            $table->string('nik');
            $table->foreign('nik')->references('nik')->on('penyewaan')->onDelete('cascade');
            $table->string('ulasan');
            $table->integer('rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};

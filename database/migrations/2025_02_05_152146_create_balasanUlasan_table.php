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
        Schema::create('balasanUlasan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_ulasan');
            $table->foreign('id_ulasan')->references('id_ulasan')->on('ulasan')->onDelete('cascade');
            $table->unsignedBigInteger('id_penyewaan');
            $table->foreign('id_penyewaan')->references('id_penyewaan')->on('penyewaan')->onDelete('cascade');
            $table->integer('parent_id');
            $table->string('ulasan');
            $table->string('reaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balasanUlasan');
    }
};

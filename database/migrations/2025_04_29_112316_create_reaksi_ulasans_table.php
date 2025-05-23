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
        Schema::create('reaksi_ulasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ulasan');
            $table->unsignedBigInteger('id_user');
            $table->enum('tipe_reaksi', ['like', 'dislike']);
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_ulasan')->references('id_ulasan')->on('ulasan')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

            // Each user can only have one reaction per review
            $table->unique(['id_ulasan', 'id_user']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reaksi_ulasan');
    }
};

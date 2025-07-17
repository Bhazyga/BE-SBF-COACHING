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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_item')->nullable()->unique(); // opsional
            $table->string('nama');
            $table->string('deskripsi');
            $table->integer('harga');
            $table->enum('tipe', ['biaya', 'perlengkapan', 'layanan'])->default('biaya');
            $table->string('gambar')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

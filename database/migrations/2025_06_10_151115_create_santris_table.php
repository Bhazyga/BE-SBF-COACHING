<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santris', function (Blueprint $table) {
            $table->id();

            $table->string('nama_lengkap', 255);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin', 20);
            $table->text('alamat_santri');
            $table->string('provinsi_santri', 100);
            $table->string('kota_kabupaten_santri', 100);

            $table->string('nama_ayah', 255);
            $table->string('telepon_ayah', 50);
            $table->string('nama_ibu', 255);
            $table->string('telepon_ibu', 50);
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->text('alamat_ortu');

            $table->string('nama_sekolah_asal', 255);
            $table->string('jenjang_pendidikan_terakhir', 50);
            $table->text('alamat_sekolah_asal');

            $table->unsignedBigInteger('grade_id')->nullable();
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santris');
    }
};

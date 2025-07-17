<?php

namespace Database\Seeders;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@azziyadah.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $santri = Santri::create([
            'nama_lengkap' => 'Bhazy Ghazalah Acyuta',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1998-07-27',
            'jenis_kelamin' => 'Laki-laki',
            'alamat_santri' => 'Jl. Kenangan No. 1',
            'provinsi_santri' => 'Jawa Timur',
            'kota_kabupaten_santri' => 'Surabaya',
            'nama_ayah' => 'Pak Bhazy',
            'telepon_ayah' => '081234567890',
            'nama_ibu' => 'Bu Tatum',
            'telepon_ibu' => '081298765432',
            'pekerjaan_ayah' => 'Karyawan',
            'pekerjaan_ibu' => 'Ibu Rumah Tangga',
            'alamat_ortu' => 'Jl. Kenangan No. 1',
            'nama_sekolah_asal' => 'SMPN 2 Surabaya',
            'jenjang_pendidikan_terakhir' => 'SMP',
            'alamat_sekolah_asal' => 'Jl. Pendidikan No. 20',
        ]);

        User::create([
            'name' => $santri->nama_lengkap,
            'email' => 'azziyadah@pengguna.com',
            'password' => Hash::make('password'),
            'role' => 'santri',
            'santri_id' => $santri->id,
        ]);
    }


}

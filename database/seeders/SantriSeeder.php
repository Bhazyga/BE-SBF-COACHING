<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('santris')->insert([
            [
                'nama_lengkap' => 'Ahmad Fauzi',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2005-04-10',
                'jenis_kelamin' => 'Laki-laki',
                'alamat_santri' => 'Jl. Merdeka No. 10, Jakarta',
                'provinsi_santri' => 'DKI Jakarta',
                'kota_kabupaten_santri' => 'Jakarta Pusat',
                'nama_ayah' => 'Budi Santoso',
                'telepon_ayah' => '081234567890',
                'nama_ibu' => 'Siti Aminah',
                'telepon_ibu' => '081298765432',
                'pekerjaan_ayah' => 'Petani',
                'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'alamat_ortu' => 'Jl. Merdeka No. 10, Jakarta',
                'nama_sekolah_asal' => 'SMP Negeri 1 Jakarta',
                'jenjang_pendidikan_terakhir' => 'SMP',
                'alamat_sekolah_asal' => 'Jl. Pendidikan No. 5, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_lengkap' => 'Nurul Huda',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2006-08-22',
                'jenis_kelamin' => 'Perempuan',
                'alamat_santri' => 'Jl. Asia Afrika No. 15, Bandung',
                'provinsi_santri' => 'Jawa Barat',
                'kota_kabupaten_santri' => 'Bandung',
                'nama_ayah' => 'Haji Ahmad',
                'telepon_ayah' => '082112345678',
                'nama_ibu' => 'Rina Sari',
                'telepon_ibu' => '082198765432',
                'pekerjaan_ayah' => 'Pedagang',
                'pekerjaan_ibu' => 'Guru',
                'alamat_ortu' => 'Jl. Asia Afrika No. 15, Bandung',
                'nama_sekolah_asal' => 'SMP Negeri 3 Bandung',
                'jenjang_pendidikan_terakhir' => 'SMP',
                'alamat_sekolah_asal' => 'Jl. Pendidikan No. 8, Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tambahkan data contoh lain sesuai kebutuhan
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'kode_item' => 'SBF-M1',
                'nama' => 'Langganan 1 Bulan',
                'deskripsi' => 'Akses penuh ke semua artikel dan fitur premium SBF Coaching selama 1 bulan.',
                'harga' => 49000,
                'tipe' => 'biaya',
                'gambar' => 'langganan-1bulan.png',
                'aktif' => true,
            ],
            // [
            //     'kode_item' => 'SBF-M6',
            //     'nama' => 'Langganan 6 Bulan',
            //     'deskripsi' => 'Akses penuh ke semua artikel dan fitur premium SBF Coaching selama 6 bulan.',
            //     'harga' => 249000, // Rp249.000
            //     'tipe' => 'biaya',
            //     'gambar' => 'langganan-6bulan.png',
            //     'aktif' => true,
            // ],
            // [
            //     'kode_item' => 'SBF-Y1',
            //     'nama' => 'Langganan 1 Tahun',
            //     'deskripsi' => 'Akses penuh ke semua artikel dan fitur premium SBF Coaching selama 1 tahun.',
            //     'harga' => 479000, // Rp479.000
            //     'tipe' => 'biaya',
            //     'gambar' => 'langganan-1tahun.png',
            //     'aktif' => true,
            // ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['kode_item' => $item['kode_item']],
                $item
            );
        }
    }
}

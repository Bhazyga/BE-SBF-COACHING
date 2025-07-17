<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('items')->insert([
            [
                'kode_item' => 'ITM001',
                'nama' => 'Item Pertama',
                'harga' => 50000,
                'deskripsi' => 'Deskripsi item pertama',
            ],
            [
                'kode_item' => 'ITM002',
                'nama' => 'Item Kedua',
                'harga' => 75000,
                'deskripsi' => 'Deskripsi item kedua',
            ],
            [
                'kode_item' => 'ITM003',
                'nama' => 'Item Ketiga',
                'harga' => 100000,
                'deskripsi' => 'Deskripsi item ketiga',
            ],
        ]);
    }
}

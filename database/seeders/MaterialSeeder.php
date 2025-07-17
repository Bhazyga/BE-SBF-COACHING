<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table('materials')->insert([
            'nama' => $faker->word,
            'deskripsi' => $faker->paragraph,
            'kategori' => $faker->word,
            'gambar' => 'sample.jpg',
            'created_at' => $faker->dateTime,
            'updated_at' => $faker->dateTime
        ]);
    }
}

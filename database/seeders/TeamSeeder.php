<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table('teams')->insert([
            'name' => $faker->word,
            'description' => $faker->paragraph,
            'instalink' => $faker->word,
            'facebooklink' => $faker->word,
            'title' => $faker->word,
            'gambar' => 'sample.jpg',
            'created_at' => $faker->dateTime,
            'updated_at' => $faker->dateTime
        ]);
    }
}

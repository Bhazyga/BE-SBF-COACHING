<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'email' => 'admin@sinau.com',
            'password' => 'password',
            'role' => 'admin'
	]);

    }


}


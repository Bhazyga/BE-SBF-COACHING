<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run()
    {
        $events = [

            // ================================
            // WEBINAR
            // ================================
            [
                'title' => 'Webinar Dasar Futsal Modern',
                'category' => 'webinar',
                'speaker' => 'Coach Ardi',
                'date' => now()->addDays(7),
                'time' => '19:00 WIB',
                'duration' => '2 Jam',
                'platform' => 'Zoom',
                'thumbnail' => 'events/sample-webinar-1.png',
                'description' => 'Pembahasan fundamental futsal modern, teknik dasar, dan metodologi latihan.',
                'is_paid' => false,
                'price' => null,
                'whatsapp_group' => 'https://chat.whatsapp.com/free-group-1',
                'extra_link' => 'https://zoom.link/webinar1',
                'tags' => ['teknik', 'basic', 'webinar'],
            ],
            [
                'title' => 'Webinar Taktikal Pressing 3-1',
                'category' => 'webinar',
                'speaker' => 'Coach Rendi',
                'date' => now()->addDays(14),
                'time' => '20:00 WIB',
                'duration' => '2.5 Jam',
                'platform' => 'Zoom',
                'thumbnail' => 'events/sample-webinar-2.png',
                'description' => 'Membahas sistem pressing high block 3-1 dan variasi skema profesional.',
                'is_paid' => true,
                'price' => 45000,
                'whatsapp_group' => 'https://chat.whatsapp.com/webinar-pressing',
                'extra_link' => 'https://zoom.link/webinar2',
                'tags' => ['taktik', 'pressing', 'webinar'],
            ],

            // ================================
            // TRAINING
            // ================================
            [
                'title' => 'Training Intensif Kiper Futsal',
                'category' => 'training',
                'speaker' => 'Coach Alfi',
                'date' => now()->addDays(5),
                'time' => '08:00 WIB',
                'duration' => '4 Jam',
                'platform' => 'Gor Futsal Jakarta',
                'thumbnail' => 'events/sample-training-1.png',
                'description' => 'Latihan intensif untuk penjaga gawang futsal: reflex, block, dan distribusi.',
                'is_paid' => true,
                'price' => 85000,
                'whatsapp_group' => 'https://chat.whatsapp.com/kiper-training',
                'extra_link' => 'https://maps.google.com/training1',
                'tags' => ['goalkeeper', 'training'],
            ],
            [
                'title' => 'Training Tactical Rotation 4-0',
                'category' => 'training',
                'speaker' => 'Coach Dimas',
                'date' => now()->addDays(10),
                'time' => '09:00 WIB',
                'duration' => '5 Jam',
                'platform' => 'Gor Bintaro',
                'thumbnail' => 'events/sample-training-2.png',
                'description' => 'Praktik langsung skema rotasi 4-0, ball movement, dan pattern play.',
                'is_paid' => false,
                'price' => null,
                'whatsapp_group' => 'https://chat.whatsapp.com/rotation-free',
                'extra_link' => 'https://maps.google.com/training2',
                'tags' => ['taktik', '4-0', 'training'],
            ],

            // ================================
            // ENGLISH CLUB
            // ================================
            [
                'title' => 'English Club – Speaking for Athletes',
                'category' => 'english_club',
                'speaker' => 'Coach Marcus',
                'date' => now()->addDays(3),
                'time' => '18:30 WIB',
                'duration' => '1.5 Jam',
                'platform' => 'Zoom',
                'thumbnail' => 'events/english-1.png',
                'description' => 'Sesi khusus peningkatan bahasa Inggris untuk atlet, fokus pada speaking dan vocabulary.',
                'is_paid' => false,
                'price' => null,
                'whatsapp_group' => 'https://chat.whatsapp.com/englishclub1',
                'extra_link' => 'https://zoom.link/english1',
                'tags' => ['english', 'speaking', 'club'],
            ],
        ];

        foreach ($events as $e) {
            Event::create([
                'title'       => $e['title'],
                'slug'        => Str::slug($e['title']) . '-' . Str::random(5),
                'category'    => $e['category'],
                'speaker'     => $e['speaker'],
                'date'        => $e['date'],
                'time'        => $e['time'],
                'duration'    => $e['duration'],
                'platform'    => $e['platform'],
                'thumbnail'   => $e['thumbnail'],
                'description' => $e['description'],
                'is_paid'     => $e['is_paid'],
                'price'       => $e['price'],
                'whatsapp_group' => $e['whatsapp_group'],
                'extra_link'  => $e['extra_link'],
                'tags'        => $e['tags'], // cast JSON otomatis
            ]);
        }
    }
}

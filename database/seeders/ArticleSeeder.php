<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Article, ArticleSection, ArticleTag, ArticleImage};
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        // Contoh data artikel
        $articles = [
            [
                'title' => 'Strategi Latihan Futsal Modern',
                'author' => 'Coach Bhazy',
                'date' => now(),
                'category' => 'Pelatihan',
                'banner' => '/storage/articles/banner-futsal.jpg',
                'tags' => ['futsal', 'latihan', 'strategi'],
                'sections' => [
                    [
                        'subtitle' => 'Pemanasan dan Taktik Awal',
                        'content' => 'Sesi ini membahas pentingnya pemanasan dan teknik dasar penguasaan bola.',
                        'youtube' => 'https://www.youtube.com/watch?v=abc123xyz',
                        'images' => [
                            '/storage/articles/sections/pemanasan.jpg',
                            '/storage/articles/sections/latihan1.jpg',
                        ],
                    ],
                    [
                        'subtitle' => 'Taktik Pertahanan',
                        'content' => 'Pelajari cara membangun pertahanan solid dan rotasi pemain.',
                        'youtube' => null,
                        'images' => [
                            '/storage/articles/sections/defense.jpg',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Nutrisi Penting untuk Atlet Futsal',
                'author' => 'Ahli Gizi Andi',
                'date' => now(),
                'category' => 'Kesehatan',
                'banner' => '/storage/articles/banner-nutrisi.jpg',
                'tags' => ['nutrisi', 'gizi', 'kesehatan'],
                'sections' => [
                    [
                        'subtitle' => 'Pola Makan Sebelum Bertanding',
                        'content' => 'Penting bagi pemain futsal untuk menjaga pola makan yang seimbang.',
                        'youtube' => 'https://www.youtube.com/watch?v=xyz789abc',
                        'images' => [
                            '/storage/articles/sections/makanan.jpg',
                        ],
                    ],
                ],
            ],
        ];

        // Loop isi data ke database
        foreach ($articles as $data) {
            $article = Article::create([
                'title' => $data['title'],
                'author' => $data['author'],
                'date' => $data['date'],
                'category' => $data['category'],
                'banner' => $data['banner'],
            ]);

            // Tags
            foreach ($data['tags'] as $tag) {
                $article->tags()->create(['name' => $tag]);
            }

            // Sections
            foreach ($data['sections'] as $sectionData) {
                $section = $article->sections()->create([
                    'subtitle' => $sectionData['subtitle'],
                    'content' => $sectionData['content'],
                    'youtube' => $sectionData['youtube'],
                ]);

                // Images
                foreach ($sectionData['images'] as $imgPath) {
                    $section->images()->create(['path' => $imgPath]);
                }
            }
        }
    }
}

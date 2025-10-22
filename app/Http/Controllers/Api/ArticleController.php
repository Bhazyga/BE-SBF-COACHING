<?php

namespace App\Http\Controllers\Api;


use App\Models\{Article, ArticleSection, ArticleTag, ArticleImage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;


class ArticleController extends Controller
{
    public function index()
    {
        return Article::with('sections.images', 'tags')->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'banner' => 'nullable|string',
            'tags' => 'array',
            'sections' => 'array'
        ]);

        // 🔹 Simpan banner sebagai file dulu (biar gak base64)
        if (!empty($data['banner']) && preg_match('/^data:image\/(\w+);base64,/', $data['banner'])) {
            $data['banner'] = $this->saveBase64Image($data['banner'], 'articles/banner');
        }

        // 🔹 Buat artikel setelah banner jadi path
        $article = Article::create($data);

        // 🔹 Simpan tags (jika ada)
        if (!empty($request->tags)) {
            foreach ($request->tags as $tag) {
                $article->tags()->create(['name' => $tag]);
            }
        }

        // 🔹 Simpan sections dan images (jika ada)
        if (!empty($request->sections)) {
            foreach ($request->sections as $section) {
                $sec = $article->sections()->create([
                    'subtitle' => $section['subtitle'] ?? null,
                    'content' => $section['content'] ?? null,
                    'youtube' => $section['youtube'] ?? null,
                ]);

                if (!empty($section['images'])) {
                    foreach ($section['images'] as $img) {
                        $path = $this->saveBase64Image($img, 'articles/sections');
                        $sec->images()->create(['path' => $path]);
                    }
                }
            }
        }

        return response()->json($article->load('sections.images', 'tags'), 201);
    }


    // public function show(Article $article)
    // {
    //     return $article->load('sections.images', 'tags');
    // }


    public function show($id)
    {
        $article = Article::with(['sections', 'tags','sections.images'])->find($id);



        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }

        return response()->json($article);
    }


    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'banner' => 'nullable|string',
            'tags' => 'array',
            'sections' => 'array'
        ]);

        // 🔹 Simpan banner sebagai file dulu
        if (!empty($data['banner']) && preg_match('/^data:image\/(\w+);base64,/', $data['banner'])) {
            $data['banner'] = $this->saveBase64Image($data['banner'], 'articles/banner');
        }

        // 🔹 Update article setelah banner diubah menjadi path
        $article->update($data);

        // Hapus tags & sections lama
        $article->tags()->delete();
        $article->sections()->delete();

        // 🔹 Simpan tags baru
        if ($request->tags) {
            foreach ($request->tags as $tag) {
                $article->tags()->create(['name' => $tag]);
            }
        }

        // 🔹 Simpan sections & images baru
        if ($request->sections) {
            foreach ($request->sections as $section) {
                $sec = $article->sections()->create([
                    'subtitle' => $section['subtitle'] ?? null,
                    'content' => $section['content'] ?? null,
                    'youtube' => $section['youtube'] ?? null,
                ]);

                if (!empty($section['images'])) {
                    foreach ($section['images'] as $img) {
                        $path = $this->saveBase64Image($img, 'articles/sections');
                        $sec->images()->create(['path' => $path]);
                    }
                }
            }
        }

        return response()->json($article->load('sections.images', 'tags'));
    }


    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }

    // private function saveBase64Image($base64, $folder)
    // {
    //     if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
    //         $data = substr($base64, strpos($base64, ',') + 1);
    //         $type = strtolower($type[1]);
    //         $data = base64_decode($data);
    //         $fileName = uniqid() . '.' . $type;
    //         $path = "$folder/$fileName";

    //         Storage::disk('public')->put($path, $data);

    //         // ✅ gunakan asset() agar hasil URL lengkap dengan domain dari APP_URL
    //         return asset("storage/$path");
    //     }
    //     return null;
    // }

    private function saveBase64Image($base64String, $path)
    {
        // Pastikan string valid base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $image = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Format gambar tidak didukung.');
            }

            $image = base64_decode($image);
            if ($image === false) {
                throw new \Exception('Base64 gambar tidak valid.');
            }

            // Buat nama file unik
            $fileName = $path . '/' . uniqid() . '.' . $type;

            // Simpan file ke storage/public
            Storage::disk('public')->put($fileName, $image);

            // Return path publik (bisa langsung dipakai di frontend)
            return asset('storage/' . $fileName);
        }

        return $base64String; // kalau sudah URL, return langsung
    }

    public function showBySlug($slug)
    {
        return Article::with('sections.images', 'tags')->where('slug', $slug)->firstOrFail();
    }


}

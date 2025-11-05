<?php

namespace App\Http\Controllers\Api;

use App\Models\{Article, ArticleSection, ArticleTag, Subscriber};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function index()
    {
        return Article::with('sections.images', 'tags')
            ->latest()
            ->get();
    }

    public function freeArticles()
    {
        return Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->latest()
            ->get();
    }

    public function filterFreeByAuthor($slug)
    {
        $articles = Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->get()
            ->filter(function ($article) use ($slug) {
                // Normalisasi nama author → slug lowercase tanpa accent
                $normalizedAuthor = Str::slug(iconv('UTF-8', 'ASCII//TRANSLIT', $article->author ?? ''));
                $normalizedSlug = Str::slug(iconv('UTF-8', 'ASCII//TRANSLIT', $slug));

                return $normalizedAuthor === $normalizedSlug;
            })
            ->values();

        if ($articles->isEmpty()) {
            return response()->json(['message' => 'Tidak ada artikel gratis untuk author ini'], 404);
        }

        return response()->json([
            'data' => $articles,
            'total' => $articles->count(),
        ]);
    }


    public function filterFreeByCategory($slug)
    {
        $category = str_replace('-', ' ', ucwords($slug));

        $articles = Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->where('category', 'LIKE', "%{$category}%")
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return response()->json($articles);
    }


    public function ArticlesHomeAndHighlight()
    {
        return Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function premiumArticles(Request $request)
    {
        $query = Article::with('sections.images', 'tags')
            ->where('is_premium', true)
            ->latest();

        // ✅ Filter berdasarkan author slug (kalau ada)
        if ($request->has('author')) {
            $authorSlug = Str::slug($request->author);
            $query->whereRaw('LOWER(REPLACE(author, " ", "-")) = ?', [$authorSlug]);
        }

        // ✅ Pagination
        $perPage = $request->get('per_page', 6); // default 6 per halaman
        $articles = $query->paginate($perPage);

        return response()->json($articles);
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
            'sections' => 'array',
            'is_premium' => 'boolean'
        ]);

        // ✅ generate slug
        $data['slug'] = Str::slug($data['title']);

        if (!empty($data['banner']) && preg_match('/^data:image/', $data['banner'])) {
            $data['banner'] = $this->saveBase64Image($data['banner'], 'articles/banner');
        }

        $article = Article::create($data);

        // Save tags
        if (!empty($request->tags)) {
            foreach ($request->tags as $tag) {
                $article->tags()->create(['name' => $tag]);
            }
        }

        // Save sections & images
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




    public function showBySlug($slug)
    {
        return Article::with('sections.images', 'tags')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function show($id) // fallback by ID
    {
        $article = Article::with('sections.images', 'tags')->find($id);

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
            'sections' => 'array',
            'is_premium' => 'boolean'
        ]);

        // ✅ regenerate slug if title changed
        if ($article->title !== $data['title']) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (!empty($data['banner']) && preg_match('/^data:image/', $data['banner'])) {
            $data['banner'] = $this->saveBase64Image($data['banner'], 'articles/banner');
        }

        $article->update($data);

        // Reset tags & sections
        $article->tags()->delete();
        $article->sections()->delete();

        // Save new tags
        if (!empty($request->tags)) {
            foreach ($request->tags as $tag) {
                $article->tags()->create(['name' => $tag]);
            }
        }

        // Save new sections
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

        return response()->json($article->load('sections.images', 'tags'));
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }

    private function saveBase64Image($base64String, $path)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $image = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);
            $image = base64_decode($image);

            $fileName = $path . '/' . uniqid() . '.' . $type;
            Storage::disk('public')->put($fileName, $image);

            return asset('storage/' . $fileName);
        }

        return $base64String;
    }


    public function filterByAuthor($slug)
    {
        $articles = Article::with('sections.images', 'tags')
            ->get()
            ->filter(function ($article) use ($slug) {
                // bikin slug dari author tapi normalize dulu accent
                $normalizedAuthor = Str::slug(iconv('UTF-8', 'ASCII//TRANSLIT', $article->author ?? ''));

                $normalizedSlug = Str::slug(iconv('UTF-8', 'ASCII//TRANSLIT', $slug));

                return $normalizedAuthor === $normalizedSlug;
            })
            ->values();

        if ($articles->isEmpty()) {
            return response()->json(['message' => 'Tidak ada artikel untuk author ini'], 404);
        }

        return response()->json([
            'data' => $articles,
            'total' => $articles->count(),
        ]);
    }

    public function filterByCategory($slug)
    {
        $category = str_replace('-', ' ', ucwords($slug));

        $articles = Article::where('category', 'LIKE', "%{$category}%")
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return response()->json($articles);
    }




    public function showPremiumPreviewBySlug($slug)
    {
        $article = Article::with('tags')
            ->where('slug', $slug)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }

        // kalau bukan premium, langsung tampil penuh aja
        if (!$article->is_premium) {
            return response()->json([
                'article' => $article->load('sections.images', 'tags'),
                'access' => 'free'
            ]);
        }

        // tampilkan hanya data preview tanpa sections detail
        return response()->json([
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'author' => $article->author,
                'date' => $article->date,
                'banner' => $article->banner,
                'category' => $article->category,
                'tags' => $article->tags,
            ],
            'access' => 'preview',
            'message' => 'Login atau berlangganan untuk membuka seluruh konten.',
        ]);
    }


    public function showPremiumBySlug($slug)
    {
        $article = Article::with('sections.images', 'tags')
            ->where('slug', $slug)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }

        // Artikel gratis → tampil penuh tanpa cek langganan
        if (!$article->is_premium) {
            return response()->json([
                'article' => $article,
                'access' => 'free',
            ]);
        }

        $user = Auth::user();
        $now = Carbon::now();

        // Belum login
        if (!$user) {
            return response()->json([
                'article' => $article->only([
                    'id', 'title', 'slug', 'author', 'created_at', 'banner', 'category'
                ]),
                'access' => 'guest',
                'message' => 'Login untuk mengakses konten premium.',
            ]);
        }

        // Cek apakah punya subscription aktif
        $subscriber = Subscriber::where('user_id', $user->id)
            ->orderByDesc('end_date')
            ->first();

        if (!$subscriber) {
            return response()->json([
                'article' => $article->only([
                    'id', 'title', 'slug', 'author', 'created_at', 'banner', 'category'
                ]),
                'access' => 'no_subscription',
                'message' => 'Anda belum memiliki langganan aktif.',
            ]);
        }

        // Kalau langganan sudah kedaluwarsa
        if (Carbon::parse($subscriber->end_date)->lt($now)) {
            return response()->json([
                'article' => $article->only([
                    'id', 'title', 'slug', 'author', 'created_at', 'banner', 'category'
                ]),
                'access' => 'expired',
                'message' => 'Langganan Anda sudah berakhir. Perpanjang untuk membuka konten ini.',
            ]);
        }

        // ✅ Langganan aktif → tampil full (semua section, image, youtube, tags)
        return response()->json([
            'article' => $article->load('sections.images', 'tags'),
            'access' => 'active',
            'plan' => $subscriber->plan,
            'valid_until' => $subscriber->end_date,
            'subscriber_id' => $subscriber->id, // tambahan kalau mau tracking di frontend
        ]);
    }

}





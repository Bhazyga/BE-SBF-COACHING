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



        // =======  Home Page Controller ===========


        public function ArticlesHomeAndHighlight()
        {
            return Article::with('sections.images', 'tags')
                ->where('is_premium', 0)
                ->latest()
                ->limit(5)
                ->get();
        }

        // ======= Ends Home Page Controller ===========



    // ======= Free Kick Controller ===========

    public function freeArticles(Request $request)
    {
        $perPage = $request->get('per_page', 6);

        $articles = Article::with(['sections.images', 'tags'])
            ->where('is_premium', 0)
            ->latest()
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    public function filterFreeByAuthor(Request $request, $slug)
    {
        $normalized = Str::slug($slug);
        $perPage = $request->get('per_page', 6);

        $articles = Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->whereRaw('LOWER(REPLACE(author, " ", "-")) = ?', [$normalized])
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    public function filterFreeByCategory(Request $request, $slug)
    {
        $perPage = $request->get('per_page', 6);

        $articles = Article::with('sections.images', 'tags')
            ->where('is_premium', 0)
            ->where('category', 'LIKE', "%$slug%")
            ->latest()
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    // ======= Ends Free Kick Controller ===========

    // ==================== Videos Controller =====================

    public function videoArticles(Request $request)
    {
        $perPage = $request->get('per_page', 6);

        $articles = Article::with(['sections.images', 'tags'])
            ->where('is_premium', 1)
            ->where('category', 'LIKE', 'video%')
            ->latest()
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    public function filterVideoByAuthor(Request $request, $slug)
    {
        $normalized = Str::slug($slug);
        $perPage = $request->get('per_page', 6);

        $articles = Article::with(['sections.images', 'tags'])
            ->where('is_premium', 1)
            ->where('category', 'LIKE', 'video%')
            ->whereRaw('LOWER(REPLACE(author, " ", "-")) = ?', [$normalized])
            ->latest()
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    public function filterVideoByCategory(Request $request, $slug)
    {
        $perPage = $request->get('per_page', 6);

        $articles = Article::with(['sections.images', 'tags'])
            ->where('is_premium', 1)
            ->where('category', 'LIKE', 'video%')
            ->where('category', 'LIKE', "%$slug%")
            ->latest()
            ->paginate($perPage);

        return response()->json(
            \App\Helpers\ApiHelper::formatArticleResponse($articles)
        );
    }

    public function showVideoBySlug($slug)
    {
        $article = Article::with(['sections.images', 'tags'])
            ->where('is_premium', 1)
            ->where('category', 'LIKE', 'video%')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($article);
    }

    // ======= Ends Videos Controller ===========

    // =======  CRUD Article Admin Controller ===========

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

    public function show($id)
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
            // hapus banner lama
            if ($article->banner) {
                $this->deleteImageFromUrl($article->banner);
            }

            $data['banner'] = $this->saveBase64Image($data['banner'], 'articles/banner');
        }

        $article->update($data);




        // Reset sections
        // 1️⃣ Ambil dulu section dan image lama
        $oldSections = $article->sections()->with('images')->get();

        // 2️⃣ Hapus file gambar lama
        foreach ($oldSections as $sec) {
            foreach ($sec->images as $img) {
                $this->deleteImageFromUrl($img->path);
            }
        }

        // 3️⃣ Hapus record images
        \App\Models\ArticleImage::whereIn(
            'section_id',
            $oldSections->pluck('id')
        )->delete();

        // 4️⃣ Hapus record section
        ArticleSection::where('article_id', $article->id)->delete();

        // Reset tags
        $article->tags()->delete();

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
        // 1. Hapus file banner
        if ($article->banner) {
            $this->deleteImageFromUrl($article->banner);
        }

        // 2. Ambil semua section milik article
        $sections = \App\Models\ArticleSection::where('article_id', $article->id)->get();

        foreach ($sections as $section) {
            // 3. Ambil semua images milik section ini
            $images = \App\Models\ArticleImage::where('section_id', $section->id)->get();

            foreach ($images as $img) {
                $this->deleteImageFromUrl($img->path);
            }

            // 4. Hapus image record di DB
            \App\Models\ArticleImage::where('section_id', $section->id)->delete();
        }

        // 5. Hapus semua section record
        \App\Models\ArticleSection::where('article_id', $article->id)->delete();

        // 6. Hapus article
        $article->delete();

        return response()->json(['message' => 'Article deleted']);
    }

    private function deleteImageFromUrl($url)
    {
        if (!$url) return;

        // Hilangkan domain — ambil path setelah /storage/
        $relativePath = str_replace(asset('storage') . '/', '', $url);

        // Hapus file dari storage/app/public
        Storage::disk('public')->delete($relativePath);
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

    // ======= Ends CRUD Article Admin Controller ===========

    // Area Teknis -> Artikel Premium


    public function premiumArticles(Request $request)
    {
        $query = Article::with('sections.images', 'tags')
            ->where('is_premium', true)
            ->latest();

        if ($request->has('author')) {
            $authorSlug = Str::slug($request->author);
            $query->whereRaw('LOWER(REPLACE(author, " ", "-")) = ?', [$authorSlug]);
        }

        $perPage = $request->get('per_page', 6);
        $articles = $query->paginate($perPage);

        return response()->json($articles);
    }

    public function filterByAuthor($slug)
    {
        $articles = Article::with('sections.images', 'tags')
            ->whereRaw('LOWER(REPLACE(author, " ", "-")) = ?', [$slug])
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return response()->json($articles);
    }

    public function filterAreaTeknisByCategory($slug)
    {
        $articles = Article::with('sections.images', 'tags')
            ->where('category', $slug)
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





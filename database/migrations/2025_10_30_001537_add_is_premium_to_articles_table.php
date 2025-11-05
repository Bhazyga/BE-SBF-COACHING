<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->boolean('is_premium')
                  ->default(false)
                  ->after('is_published');
        });

        // isi slug dari title untuk existing data
        $articles = DB::table('articles')->get();
        foreach ($articles as $article) {
            $slug = $article->title
                ? Str::slug($article->title)
                : 'artikel-' . $article->id;

            $originalSlug = $slug;
            $counter = 1;
            while (DB::table('articles')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            DB::table('articles')
                ->where('id', $article->id)
                ->update(['slug' => $slug]);
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

};

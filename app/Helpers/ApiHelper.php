<?php

namespace App\Helpers;

class ApiHelper
{
    public static function formatArticleResponse($articles)
    {
        // Hitung total authors dari data hasil filter, bukan seluruh DB
        $uniqueAuthors = collect($articles->items())
            ->pluck('author')
            ->unique()
            ->count();

        return [
            'data' => $articles->items(),
            'current_page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
            'total' => $articles->total(),
            'total_authors' => $uniqueAuthors,
        ];
    }
}

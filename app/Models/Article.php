<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'date', 'category', 'banner', 'is_published'
    ];

    public function sections()
    {
        return $this->hasMany(ArticleSection::class);
    }

    public function tags()
    {
        return $this->hasMany(ArticleTag::class);
    }
    // public function images()
    // {
    //     return $this->hasMany(ArticleImage::class);
    // }
}

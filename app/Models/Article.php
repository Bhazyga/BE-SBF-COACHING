<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'author', 'date', 'category', 'banner', 'is_published', 'is_premium'
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function sections()
    {
        return $this->hasMany(ArticleSection::class);
    }

    public function tags()
    {
        return $this->hasMany(ArticleTag::class);
    }
}

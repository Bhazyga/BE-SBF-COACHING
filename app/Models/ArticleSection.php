<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleSection extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'subtitle', 'content', 'youtube'];


    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function images()
    {
        return $this->hasMany(ArticleImage::class, 'section_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'speaker',
        'date',
        'time',
        'duration',
        'platform',
        'thumbnail',
        'description',
        'extra_link',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'date' => 'date',
    ];
}

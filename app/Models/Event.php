<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'is_paid',
        'price',
        'whatsapp_group',
        'extra_link',
        'tags',
    ];

    protected $casts = [
        'date' => 'date',
        'tags' => 'array',
        'is_paid' => 'boolean',
    ];

    // Relasi ke registrations
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isFree()
    {
        return !$this->is_paid || $this->price === null || $this->price == 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    // Nama tabel (opsional karena Laravel otomatis pakai plural)
    protected $table = 'subscribers';

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kelas',
        'tahun_ajaran',
    ];

    /**
     * Relasi ke santris
     * Satu kelas (grade) bisa punya banyak santri
     */
    public function santris()
    {
        return $this->hasMany(Santri::class);
    }
}

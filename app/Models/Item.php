<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // Nama tabel (opsional kalau sudah sesuai konvensi)
    protected $table = 'items';

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'kode_item',
        'nama',
        'deskripsi',
        'harga',
        'tipe',
        'gambar',
        'aktif',
    ];

    // Casting tipe data
    protected $casts = [
        'aktif' => 'boolean',
    ];

    // Relasi: satu item bisa punya banyak transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'item_id');
    }
}

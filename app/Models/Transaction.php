<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Nama tabel (opsional, kalau sudah sesuai konvensi Laravel biasanya gak perlu)
    protected $table = 'transactions';

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'subscriber_id',
        'item_id',
        'transaction_id',
        'midtrans_order_id',
        'jumlah',
        'total_harga',
        'status',
        'payment_type',
        'midtrans_response',
        'transaction_time',
        'payment_time',
    ];

    // Casting tipe data khusus
    protected $casts = [
        'midtrans_response' => 'array', // json jadi array otomatis
        'transaction_time' => 'datetime',
        'payment_time' => 'datetime',
    ];

    // Relasi ke Subscriber (banyak transaksi dimiliki oleh satu subs)
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id');
    }

    // Relasi ke Item (banyak transaksi dimiliki oleh satu item)
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}

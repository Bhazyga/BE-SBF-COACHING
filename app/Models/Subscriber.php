<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $table = 'subscribers';

    protected $fillable = [
        'user_id',
        'plan',
        'start_date',
        'end_date',
        'transaction_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

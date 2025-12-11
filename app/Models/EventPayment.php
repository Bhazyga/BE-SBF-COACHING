<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'order_id',
        'amount',
        'status',
        'payment_type',
        'transaction_id',
        'midtrans_response',
        'transaction_time',
        'payment_time',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


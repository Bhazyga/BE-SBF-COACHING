<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'payment_status',
        'paid_at',
        'midtrans_order_id',
        'extra_link_sent',
        'joined_event',
        'joined_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'joined_at' => 'datetime',
        'extra_link_sent' => 'boolean',
        'joined_event' => 'boolean',
    ];

    // RELASI
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payment()
    {
        return $this->hasOne(EventPayment::class, 'order_id', 'midtrans_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Untuk event gratis dianggap paid otomatis
    public function markAsFree()
    {
        $this->payment_status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    // Check apakah user sudah join
    public function hasJoined()
    {
        return $this->joined_event === true;
    }
}

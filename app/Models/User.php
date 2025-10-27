<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail {
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];


    public function generateEmailOtp()
    {
        $this->email_otp = rand(100000, 999999);
        $this->email_otp_expires_at = now()->addMinutes(1);
        $this->save();
    }


    // 1 User memiliki 1 Subscriber
    public function subscriber()
    {
        return $this->hasOne(Subscriber::class);
    }
}

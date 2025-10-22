<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'http://localhost:8000/api/login'
        "https://api.sbf-coaching.com/login",
        "https://api.sbf-coaching.com/api/users",
        "https://api.sbf-coaching.com/api/materials",
        "https://760a77dda0bc.ngrok-free.app/api/midtrans/notification",

    ];
}

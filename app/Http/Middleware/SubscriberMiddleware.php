<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriberMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // pastikan login dulu
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // admin tetap boleh, subscriber juga boleh
        if ($user->role === 'admin' || $user->is_subscriber === true) {
            return $next($request);
        }

        return response()->json(['message' => 'Access denied. Only active subscribers or admins can view premium articles.'], 403);
    }
}

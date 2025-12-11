<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class EventRegistrationController extends Controller
{
    public function register(Request $req)
    {
        $user = Auth::user();
        $event = Event::findOrFail($req->event_id);

        // Cek apakah sudah daftar
        $existing = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json([
                "message" => "Sudah terdaftar",
                "already_registered" => true,
                "extra_link_sent" => $existing->extra_link_sent
            ]);
        }

        // Kalau event gratis → otomatis paid
        $status = $event->isFree() ? 'paid' : 'pending';

        $reg = EventRegistration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'payment_status' => $status,
            'paid_at' => $event->isFree() ? now() : null,
            'extra_link_sent' => false,
        ]);

        // Kirim email Zoom / WA group jika gratis
        if ($event->isFree() && $event->extra_link) {
            Mail::to($user->email)->send(new \App\Mail\EventExtraLink($event));
            $reg->update(['extra_link_sent' => true]);
        }

        return response()->json([
            "message" => "Registration successful",
            "registration" => $reg
        ]);
    }

    public function registrationStatus($event_id)
    {
        $user = Auth::user();

        $exists = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event_id)
            ->exists();

        return response()->json([
            "registered" => $exists
        ]);
    }

}

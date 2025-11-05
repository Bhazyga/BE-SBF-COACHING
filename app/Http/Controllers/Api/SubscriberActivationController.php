<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriberActivationController extends Controller
{
    public function activate(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:1_month,6_months,12_months',
        ]);

        $user = User::findOrFail($id);

        if ($user->role !== 'subscriber') {
            return response()->json(['message' => 'User ini bukan subscriber'], 422);
        }

        $now = Carbon::now();
        $subscriber = Subscriber::where('user_id', $user->id)->first();

        // Tentukan durasi dari plan
        $monthsToAdd = match ($request->plan) {
            '1_month' => 1,
            '6_months' => 6,
            '12_months' => 12,
            default => 1,
        };

        if ($subscriber) {
            $currentEnd = $subscriber->end_date ? Carbon::parse($subscriber->end_date) : null;

            // Kalau masih aktif, extend dari end_date lama
            if ($currentEnd && $currentEnd->gte($now)) {
                $newStart = $currentEnd->copy();
                $newEnd = $currentEnd->copy()->addMonths($monthsToAdd);
            } else {
                // Kalau udah expired, mulai dari sekarang
                $newStart = $now;
                $newEnd = $now->copy()->addMonths($monthsToAdd);
            }

            $subscriber->update([
                'plan' => $request->plan,
                'start_date' => $newStart,
                'end_date' => $newEnd,
                'transaction_id' => 'manual',
            ]);
        } else {
            // Belum ada subscriber record
            $newStart = $now;
            $newEnd = $now->copy()->addMonths($monthsToAdd);

            $subscriber = Subscriber::create([
                'user_id' => $user->id,
                'plan' => $request->plan,
                'start_date' => $newStart,
                'end_date' => $newEnd,
                'transaction_id' => 'manual',
            ]);
        }

        return response()->json([
            'message' => 'Subscriber berhasil diaktifkan / diperpanjang manual',
            'subscription' => $subscriber,
        ]);
    }
}

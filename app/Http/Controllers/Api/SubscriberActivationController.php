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

        ]);

        $user = User::findOrFail($id);

        if ($user->role !== 'subscriber') {
            return response()->json(['message' => 'User ini bukan subscriber'], 422);
        }

        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addMonths($request->duration_months);

        $subscriber = Subscriber::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan' => $request->plan,         // enum monthly/yearly
                'start_date' => $startDate,
                'end_date' => $endDate,
                'transaction_id' => null          // manual aktivasi
            ]
        );

        return response()->json([
            'message' => 'Subscriber berhasil diaktifkan manual',
            'subscription' => $subscriber
        ]);
    }
}

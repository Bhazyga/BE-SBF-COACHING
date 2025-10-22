<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriberActivationController extends Controller
{
    public function activate(Request $request, $id)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
        ]);

        // 1. Cari subscriber
        $subscriber = Subscriber::findOrFail($id);

        // 2. Set grade_id (aktivasi kelas)
        $subscriber->grade_id = $request->grade_id;
        $subscriber->save();

        // 3. Update user yang belum punya subscriber_id
        $user = User::where('email', $subscriber->email)
            ->whereNull('subscriber_id')
            ->first();

        if ($user) {
            $user->subscriber_id = $subscriber->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Subscriber berhasil diaktifkan.',
            'subscriber' => $subscriber,
            'user' => $user,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = User::where('role', 'subscriber')
            ->with('subscriber')
            ->get();

        return response()->json($subscribers);
    }

    public function show($id)
    {
        $subscriber = User::with('subscriber')->findOrFail($id);
        return response()->json($subscriber);
    }

    public function update(Request $request, $id)
    {
        $subscriber = User::findOrFail($id);

        $subscriber->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Subscriber diperbarui',
            'data' => $subscriber->load('subscriber')
        ]);
    }

    public function destroy($id)
    {
        $subscriber = User::findOrFail($id);
        $subscriber->delete();

        return response()->json(['message' => 'Subscriber dihapus']);
    }

    public function profile($id)
    {
        $subscriber = User::with('subscriber')->findOrFail($id);
        return response()->json($subscriber);
    }
}

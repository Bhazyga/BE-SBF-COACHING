<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriberController extends Controller
{
    /**
     * Tampilkan daftar subscriber.
     * GET /api/subscribers?search=nama
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $subscribers = Subscriber::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($subscribers, Response::HTTP_OK);
    }

    /**
     * Tambahkan subscriber baru.
     * POST /api/subscribers
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:subscribers,email',
            'phone'  => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
        ]);

        $subscriber = Subscriber::create($data);

        return response()->json($subscriber, Response::HTTP_CREATED);
    }

    /**
     * Detail subscriber tertentu.
     * GET /api/subscribers/{id}
     */
    public function show(Subscriber $subscriber)
    {
        return response()->json($subscriber, Response::HTTP_OK);
    }

    /**
     * Update data subscriber.
     * PUT /api/subscribers/{id}
     */
    public function update(Request $request, Subscriber $subscriber)
    {
        $data = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'email'  => 'sometimes|required|email|unique:subscribers,email,' . $subscriber->id,
            'phone'  => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
        ]);

        $subscriber->update($data);

        return response()->json($subscriber, Response::HTTP_OK);
    }

    /**
     * Hapus subscriber.
     * DELETE /api/subscribers/{id}
     */
    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

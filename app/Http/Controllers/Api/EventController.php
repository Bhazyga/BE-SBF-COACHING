<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // ======================
    // ADMIN CRUD
    // ======================

    public function index(Request $req)
    {
        return Event::latest()->paginate(
            $req->get('per_page', 6)
        );
    }

    // PAKAI SLUG (BUKAN ID)
    public function show($slug)
    {
        return Event::where('slug', $slug)->firstOrFail();
    }

    public function store(Request $req)
    {
        $data = $this->validateData($req);

        $data['slug'] = Str::slug($data['title']) . "-" . Str::random(5);

        if ($req->thumbnail) {
            $data['thumbnail'] = $this->saveBase64($req->thumbnail);
        }

        return Event::create($data);
    }

    public function update(Request $req, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $data  = $this->validateData($req);

        if ($data['title'] !== $event->title) {
            $data['slug'] = Str::slug($data['title']) . "-" . Str::random(5);
        }

        // thumbnail update logic
        if ($req->thumbnail) {

            // CASE 1: Admin upload gambar baru (base64)
            if (Str::startsWith($req->thumbnail, 'data:image')) {

                // hapus gambar lama
                if ($event->thumbnail && Storage::exists($event->thumbnail)) {
                    Storage::delete($event->thumbnail);
                }

                // simpan baru
                $data['thumbnail'] = $this->saveBase64($req->thumbnail);
            }

            // CASE 2: Admin tidak ubah thumbnail (filename lama)
            else {
                // jangan ubah apapun
                unset($data['thumbnail']);
            }

        } else {

            // CASE 3: Admin hapus thumbnail
            if ($event->thumbnail && Storage::exists($event->thumbnail)) {
                Storage::delete($event->thumbnail);
            }

            $data['thumbnail'] = null;
        }

        $event->update($data);

        return $event;
    }


    public function destroy($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if ($event->thumbnail && Storage::exists($event->thumbnail)) {
            Storage::delete($event->thumbnail);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }

    // ======================
    // PUBLIC ROUTES
    // ======================

    public function publicIndex(Request $req)
    {
        $query = Event::select([
            'id', 'title', 'slug', 'category', 'speaker',
            'date', 'time', 'duration', 'platform', 'thumbnail',
            'description', 'is_paid', 'price', 'tags', 'created_at'
        ]);

        if ($req->category) {
            $query->where('category', $req->category);
        }

        return $query->latest()->paginate(
            $req->get('per_page', 12)
        );
    }


    public function listWebinars(Request $req)
    {
        return Event::where('category', 'webinar')
            ->latest()
            ->paginate($req->get('per_page', 6));
    }

    public function listTrainings(Request $req)
    {
        return Event::where('category', 'training')
            ->latest()
            ->paginate($req->get('per_page', 6));
    }

    public function listEnglishClub(Request $req)
    {
        return Event::where('category', 'english_club')
            ->latest()
            ->paginate($req->get('per_page', 6));
    }

    public function detailBySlug($slug)
    {
        return Event::select([
            'id', 'title', 'slug', 'category', 'speaker',
            'date', 'time', 'duration', 'platform', 'thumbnail',
            'description', 'is_paid', 'price', 'tags', 'created_at'
        ])
        ->where('slug', $slug)
        ->firstOrFail();
    }


    // ======================
    // HELPER FUNCTIONS
    // ======================

    private function validateData(Request $req)
    {
        return $req->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'required|in:webinar,training,english_club',
            'speaker'       => 'required|string|max:255',
            'date'          => 'required|date',
            'time'          => 'nullable|string',
            'duration'      => 'nullable|string',
            'platform'      => 'nullable|string',
            'thumbnail'     => 'nullable|string',
            'description'   => 'required|string',
            'is_paid'       => 'boolean',
            'price'         => 'nullable|integer',
            'whatsapp_group'=> 'nullable|string',
            'extra_link'    => 'nullable|string',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string',
        ]);
    }

    private function saveBase64($base64)
    {
        if (!str_starts_with($base64, 'data:image')) return null;

        $image = explode(',', $base64)[1];
        $path  = 'events/' . uniqid() . '.png';

        Storage::put($path, base64_decode($image));

        return $path;
    }
}

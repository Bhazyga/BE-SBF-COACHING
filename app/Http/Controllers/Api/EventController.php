<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return Event::orderBy('date', 'asc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "title" => "required",
            "type" => "required|in:webinar,training",
            "description" => "nullable",
            "date" => "required|date",
            "time" => "required",
            "banner" => "nullable|string",
            "location" => "nullable|string",
            "price" => "nullable|numeric",
            "capacity" => "nullable|integer",
            "zoom_link" => "nullable|string",
        ]);

        $event = Event::create($data);

        return response()->json($event);
    }

    public function show($id)
    {
        return Event::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            "title" => "required",
            "type" => "required|in:webinar,training",
            "description" => "nullable",
            "date" => "required|date",
            "time" => "required",
            "banner" => "nullable|string",
            "location" => "nullable|string",
            "price" => "nullable|numeric",
            "capacity" => "nullable|integer",
            "zoom_link" => "nullable|string",
        ]);

        $event->update($data);

        return response()->json($event);
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();

        return response()->json(["message" => "Event deleted"]);
    }
}

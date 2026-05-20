<?php

namespace App\Http\Controllers\API\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // List events owned by authenticated organizer
    public function index(Request $request)
    {
        $user = $request->user();
        $events = Event::where('organizer_id', $user->id)->get();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_name' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $data['organizer_id'] = $request->user()->id;

        $event = Event::create($data);
        return response()->json($event, 201);
    }

    public function show(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);

        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location_name' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $event->update($v->validated());
        return response()->json($event);
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }

    public function uploadCover(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);

        $v = Validator::make($request->all(), [
            'cover' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $file = $request->file('cover');
        $path = $file->store('event_covers', 'public');

        // delete previous cover if exists
        if ($event->cover_path) {
            Storage::disk('public')->delete($event->cover_path);
        }

        $event->cover_path = $path;
        $event->save();

        return response()->json(['cover_path' => $path]);
    }

    public function uploadGallery(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);

        $v = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $stored = [];
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('event_gallery', 'public');
            $img = \App\Models\EventImage::create([
                'event_id' => $event->id,
                'path' => $path,
            ]);
            $stored[] = $img;
        }

        return response()->json(['images' => $stored], 201);
    }

    public function deleteGallery(Request $request, Event $event, \App\Models\EventImage $image)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        if ($image->event_id !== $event->id) {
            return response()->json(['message' => 'Image does not belong to this event'], 404);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted']);
    }

    protected function authorizeOwnership(int $userId, Event $event)
    {
        if ($event->organizer_id !== $userId) {
            abort(403, 'Unauthorized');
        }
    }
}

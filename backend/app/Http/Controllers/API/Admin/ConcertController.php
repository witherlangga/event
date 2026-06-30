<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConcertController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderByDesc('starts_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar konser berhasil dimuat.',
            'data' => $events,
        ]);
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
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $event = Event::create($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Konser berhasil dibuat.',
            'data' => $event,
        ], 201);
    }

    public function show(Event $event)
    {
        $event->load(['ticketTypes', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Detail konser berhasil dimuat.',
            'data' => $event,
        ]);
    }

    public function update(Request $request, Event $event)
    {
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
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $event->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Konser berhasil diperbarui.',
            'data' => $event,
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Konser berhasil dihapus.',
            'data' => null,
        ]);
    }

    public function uploadCover(Request $request, Event $event)
    {
        $v = Validator::make($request->all(), [
            'cover' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $path = $request->file('cover')->store('event_covers', 'public');

        if ($event->cover_path) {
            Storage::disk('public')->delete($event->cover_path);
        }

        $event->cover_path = $path;
        $event->save();

        return response()->json([
            'success' => true,
            'message' => 'Cover konser berhasil diunggah.',
            'data' => ['cover_path' => $path],
        ]);
    }

    public function uploadGallery(Request $request, Event $event)
    {
        $v = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $stored = [];
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('event_gallery', 'public');
            $stored[] = EventImage::create([
                'event_id' => $event->id,
                'path' => $path,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Galeri konser berhasil diunggah.',
            'data' => ['images' => $stored],
        ], 201);
    }

    public function deleteGallery(Event $event, EventImage $image)
    {
        if ($image->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak termasuk konser ini.'], 404);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus.',
            'data' => null,
        ]);
    }
}

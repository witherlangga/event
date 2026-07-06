<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    public function index()
    {
        $album = Album::firstOrCreate(
            ['title' => 'Daftar Music'],
            ['description' => 'Playlist music untuk halaman web', 'is_active' => true, 'sort_order' => 1]
        );

        $songs = $album->songs()->orderBy('track_number')->get();

        return view('admin.settings.music', compact('album', 'songs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'track_number' => 'nullable|integer|min:1',
            'audio_file' => 'required|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $album = Album::firstOrCreate(
            ['title' => 'Daftar Music'],
            ['description' => 'Playlist music untuk halaman web', 'is_active' => true, 'sort_order' => 1]
        );

        $trackNumber = $data['track_number'] ?? ($album->songs()->max('track_number') + 1);
        $path = $request->file('audio_file')->store('songs', 'public');

        Song::create([
            'album_id' => $album->id,
            'title' => $data['title'],
            'streaming_url' => $path,
            'track_number' => $trackNumber,
            'is_active' => true,
        ]);

        return redirect()->route('admin.settings.music')->with('success', 'Song berhasil ditambahkan.');
    }

    public function destroy(Song $song)
    {
        if ($song->album->title !== 'Daftar Music') {
            abort(404);
        }

        Storage::disk('public')->delete($song->streaming_url);
        $song->delete();

        return redirect()->route('admin.settings.music')->with('success', 'Song berhasil dihapus.');
    }

    public function toggleActive(Song $song)
    {
        if ($song->album->title !== 'Daftar Music') {
            abort(404);
        }

        $song->update(['is_active' => ! $song->is_active]);

        return redirect()->route('admin.settings.music')->with('success', 'Status lagu berhasil diperbarui.');
    }
}

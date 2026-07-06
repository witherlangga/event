<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    /**
     * Menampilkan halaman music dengan daftar lagu aktif
     * Terhubung dengan tombol "Listen Now" di halaman home
     */
    public function index()
    {
        // Ambil album "Daftar Music" yang berisi playlist untuk halaman web
        $album = Album::where('title', 'Daftar Music')
            ->where('is_active', true)
            ->first();

        // Jika album tidak ada, buat album baru
        if (!$album) {
            $album = Album::create([
                'title' => 'Daftar Music',
                'description' => 'Playlist music untuk halaman web',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        // Ambil semua lagu aktif dari album, diurutkan berdasarkan track_number
        $songs = $album->songs()
            ->where('is_active', true)
            ->orderBy('track_number')
            ->get();

        return view('music.index', compact('songs', 'album'));
    }

    /**
     * API endpoint untuk mendapatkan daftar musik dalam format JSON
     * Berguna untuk mobile app atau frontend yang membutuhkan JSON response
     */
    public function getSongs()
    {
        $album = Album::where('title', 'Daftar Music')
            ->where('is_active', true)
            ->first();

        if (!$album) {
            return response()->json(['songs' => [], 'message' => 'No music available'], 200);
        }

        $songs = $album->songs()
            ->where('is_active', true)
            ->orderBy('track_number')
            ->get()
            ->map(function ($song) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'duration' => $song->duration_seconds,
                    'url' => asset('storage/' . $song->streaming_url),
                    'track_number' => $song->track_number,
                    'album_id' => $song->album_id,
                ];
            });

        return response()->json([
            'success' => true,
            'songs' => $songs,
            'album' => [
                'id' => $album->id,
                'title' => $album->title,
                'description' => $album->description,
            ],
        ], 200);
    }

    /**
     * Mendapatkan detail satu lagu untuk play individual
     */
    public function getSong(Song $song)
    {
        // Verifikasi lagu tersebut aktif dan berada di album "Daftar Music"
        if (!$song->is_active || $song->album->title !== 'Daftar Music') {
            return response()->json(['message' => 'Song not found'], 404);
        }

        return response()->json([
            'success' => true,
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'duration' => $song->duration_seconds,
                'url' => asset('storage/' . $song->streaming_url),
                'track_number' => $song->track_number,
                'album' => [
                    'id' => $song->album->id,
                    'title' => $song->album->title,
                ],
            ],
        ], 200);
    }
}

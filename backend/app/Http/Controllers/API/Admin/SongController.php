<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SongController extends Controller
{
    public function store(Request $request, Album $album)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'duration_seconds' => 'nullable|integer|min:0',
            'streaming_url' => 'nullable|url|max:500',
            'track_number' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = $v->validated();
        $data['album_id'] = $album->id;

        $song = Song::create($data);

        return $this->success($song, 'Lagu berhasil ditambahkan.', 201);
    }

    public function update(Request $request, Album $album, Song $song)
    {
        $this->ensureBelongsToAlbum($song, $album);

        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'duration_seconds' => 'nullable|integer|min:0',
            'streaming_url' => 'nullable|url|max:500',
            'track_number' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $song->update($v->validated());

        return $this->success($song->fresh(), 'Lagu berhasil diperbarui.');
    }

    public function destroy(Album $album, Song $song)
    {
        $this->ensureBelongsToAlbum($song, $album);
        $song->delete();

        return $this->success(null, 'Lagu berhasil dihapus.');
    }

    protected function ensureBelongsToAlbum(Song $song, Album $album): void
    {
        if ($song->album_id !== $album->id) {
            abort(404, 'Lagu tidak ditemukan pada album ini.');
        }
    }
}

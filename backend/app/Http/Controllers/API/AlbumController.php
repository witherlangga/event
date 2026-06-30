<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Album;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::with('songs')
            ->where('is_active', true)
            ->orderByDesc('released_at')
            ->orderBy('sort_order')
            ->get();

        return $this->success($albums, 'Discography berhasil dimuat.');
    }

    public function show(Album $album)
    {
        if (! $album->is_active) {
            return $this->error('Album tidak tersedia.', 404);
        }

        $album->load('songs');

        return $this->success($album, 'Detail album berhasil dimuat.');
    }
}

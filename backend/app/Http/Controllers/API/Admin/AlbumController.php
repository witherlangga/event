<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    public function index()
    {
        return $this->success(
            Album::with('songs')->orderByDesc('released_at')->get(),
            'Daftar album berhasil dimuat.'
        );
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'released_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('cover')->toArray();
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('album_covers', 'public');
        }

        $album = Album::create($data);

        return $this->success($album, 'Album berhasil ditambahkan.', 201);
    }

    public function update(Request $request, Album $album)
    {
        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'released_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('cover')->toArray();
        if ($request->hasFile('cover')) {
            if ($album->cover_path) {
                Storage::disk('public')->delete($album->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('album_covers', 'public');
        }

        $album->update($data);

        return $this->success($album->fresh()->load('songs'), 'Album berhasil diperbarui.');
    }

    public function destroy(Album $album)
    {
        if ($album->cover_path) {
            Storage::disk('public')->delete($album->cover_path);
        }
        $album->delete();

        return $this->success(null, 'Album berhasil dihapus.');
    }
}

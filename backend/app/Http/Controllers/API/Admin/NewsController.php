<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        return $this->success(
            NewsPost::orderByDesc('published_at')->orderByDesc('created_at')->get(),
            'Daftar berita berhasil dimuat.'
        );
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news_posts,slug',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('cover')->toArray();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']).'-'.Str::random(4);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('news_covers', 'public');
        }

        $post = NewsPost::create($data);

        return $this->success($post, 'Berita berhasil ditambahkan.', 201);
    }

    public function update(Request $request, NewsPost $newsPost)
    {
        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news_posts,slug,'.$newsPost->id,
            'excerpt' => 'nullable|string',
            'body' => 'sometimes|required|string',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('cover')->toArray();
        if ($request->hasFile('cover')) {
            if ($newsPost->cover_path) {
                Storage::disk('public')->delete($newsPost->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('news_covers', 'public');
        }

        $newsPost->update($data);

        return $this->success($newsPost->fresh(), 'Berita berhasil diperbarui.');
    }

    public function destroy(NewsPost $newsPost)
    {
        if ($newsPost->cover_path) {
            Storage::disk('public')->delete($newsPost->cover_path);
        }
        $newsPost->delete();

        return $this->success(null, 'Berita berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;

class NewsController extends Controller
{
    public function index()
    {
        $posts = NewsPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->get();

        return $this->success($posts, 'Berita berhasil dimuat.');
    }

    public function show(NewsPost $newsPost)
    {
        if (! $newsPost->is_published || ! $newsPost->published_at || $newsPost->published_at->isFuture()) {
            return $this->error('Berita tidak tersedia.', 404);
        }

        return $this->success($newsPost, 'Detail berita berhasil dimuat.');
    }
}

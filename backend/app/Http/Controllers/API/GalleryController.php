<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;

class GalleryController extends Controller
{
    public function index()
    {
        $items = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($items, 'Galeri berhasil dimuat.');
    }
}

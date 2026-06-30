<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    public function index()
    {
        return $this->success(
            GalleryItem::orderBy('sort_order')->orderByDesc('created_at')->get(),
            'Daftar galeri berhasil dimuat.'
        );
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('image')->toArray();
        $data['image_path'] = $request->file('image')->store('band_gallery', 'public');

        $item = GalleryItem::create($data);

        return $this->success($item, 'Item galeri berhasil ditambahkan.', 201);
    }

    public function update(Request $request, GalleryItem $galleryItem)
    {
        $v = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('image')->toArray();
        if ($request->hasFile('image')) {
            if ($galleryItem->image_path) {
                Storage::disk('public')->delete($galleryItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('band_gallery', 'public');
        }

        $galleryItem->update($data);

        return $this->success($galleryItem->fresh(), 'Item galeri berhasil diperbarui.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        if ($galleryItem->image_path) {
            Storage::disk('public')->delete($galleryItem->image_path);
        }
        $galleryItem->delete();

        return $this->success(null, 'Item galeri berhasil dihapus.');
    }
}

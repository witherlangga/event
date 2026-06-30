<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            ['title' => 'Live at GBK', 'caption' => 'Momen spesial konser GBK 2025', 'sort_order' => 1],
            ['title' => 'Studio Session', 'caption' => 'Behind the scenes rekaman album', 'sort_order' => 2],
            ['title' => 'Meet the Fans', 'caption' => 'Fan meeting setelah soundcheck', 'sort_order' => 3],
        ];

        foreach ($items as $item) {
            GalleryItem::updateOrCreate(
                ['title' => $item['title']],
                array_merge($item, [
                    'image_path' => 'band_gallery/placeholder.jpg',
                    'is_active' => true,
                ])
            );
        }
    }
}

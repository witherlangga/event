<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        NewsPost::updateOrCreate(
            ['slug' => 'neon-horizon-live-tour-2026'],
            [
                'title' => 'Neon Horizon Live Tour 2026 Resmi Diumumkan',
                'excerpt' => 'Tur konser resmi dimulai bulan depan. Tiket tersedia di aplikasi.',
                'body' => 'Neon Horizon dengan bangga mengumumkan Live Tour 2026. Fans dapat membeli tiket digital langsung melalui aplikasi resmi band. Jangan lewatkan pengalaman live music terbaik!',
                'published_at' => now()->subDays(3),
                'is_published' => true,
            ]
        );

        NewsPost::updateOrCreate(
            ['slug' => 'album-baru-sedang-diproduksi'],
            [
                'title' => 'Album Baru Sedang Diproduksi',
                'excerpt' => 'Band sedang mengerjakan materi album studio berikutnya.',
                'body' => 'Tim Neon Horizon sedang berada di studio untuk album studio kedua. Stay tuned untuk update single terbaru.',
                'published_at' => now()->subDays(10),
                'is_published' => true,
            ]
        );
    }
}

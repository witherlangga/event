<?php

namespace Database\Seeders;

use App\Models\BandProfile;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BandProfileSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        BandProfile::updateOrCreate(
            ['name' => 'Neon Horizon'],
            [
                'bio' => 'Neon Horizon adalah band rock alternatif resmi. Ikuti konser kami dan dapatkan tiket digital langsung dari aplikasi.',
                'genre' => 'Alternative Rock',
                'formed_year' => '2018',
                'social_links' => [
                    'instagram' => 'https://instagram.com/neonhorizon',
                    'youtube' => 'https://youtube.com/@neonhorizon',
                    'spotify' => 'https://open.spotify.com/artist/neonhorizon',
                ],
            ]
        );
    }
}

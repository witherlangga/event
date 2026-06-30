<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Song;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $album = Album::updateOrCreate(
            ['title' => 'Midnight Echoes'],
            [
                'description' => 'Album debut Neon Horizon dengan nuansa alternative rock modern.',
                'released_at' => '2023-06-15',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $songs = [
            ['title' => 'Neon Lights', 'track_number' => 1, 'duration_seconds' => 245],
            ['title' => 'Horizon Line', 'track_number' => 2, 'duration_seconds' => 198],
            ['title' => 'Echo Chamber', 'track_number' => 3, 'duration_seconds' => 267],
            ['title' => 'City Pulse', 'track_number' => 4, 'duration_seconds' => 221],
        ];

        foreach ($songs as $song) {
            Song::updateOrCreate(
                ['album_id' => $album->id, 'title' => $song['title']],
                array_merge($song, ['is_active' => true])
            );
        }

        $album2 = Album::updateOrCreate(
            ['title' => 'Live Sessions Vol. 1'],
            [
                'description' => 'Rekaman live akustik dari tur 2024.',
                'released_at' => '2024-11-01',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        Song::updateOrCreate(
            ['album_id' => $album2->id, 'title' => 'Unplugged Horizon'],
            ['track_number' => 1, 'duration_seconds' => 312, 'is_active' => true]
        );
    }
}

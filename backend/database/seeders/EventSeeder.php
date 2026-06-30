<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Event::updateOrCreate(
            ['title' => 'Neon Horizon Live Tour 2026'],
            [
                'description' => 'Konser resmi Neon Horizon. Dapatkan tiket digital dan nikmati pengalaman live music terbaik.',
                'location_name' => 'Gelora Bung Karno',
                'location_address' => 'Jl. Pintu Satu Senayan, Jakarta',
                'location_lat' => -6.2185,
                'location_lng' => 106.8028,
                'starts_at' => now()->addDays(30),
                'ends_at' => now()->addDays(30)->addHours(4),
                'capacity' => 5000,
                'is_active' => true,
            ]
        );

        Event::updateOrCreate(
            ['title' => 'Neon Horizon Acoustic Night'],
            [
                'description' => 'Malam akustik intimate bersama Neon Horizon. Tempat terbatas.',
                'location_name' => 'Kafe Musik Senayan',
                'location_address' => 'Jl. Asia Afrika, Jakarta',
                'location_lat' => -6.2250,
                'location_lng' => 106.7990,
                'starts_at' => now()->addDays(14),
                'ends_at' => now()->addDays(14)->addHours(3),
                'capacity' => 200,
                'is_active' => true,
            ]
        );
    }
}

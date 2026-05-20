<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@example.com')->first();
        if (! $organizer) {
            return;
        }

        Event::updateOrCreate(
            ['title' => 'Contoh Event Organizer', 'organizer_id' => $organizer->id],
            [
                'description' => 'Event contoh yang dibuat untuk organizer default.',
                'location_name' => 'Kampus Contoh',
                'location_address' => 'Jl. Contoh No.1',
                'location_lat' => -6.200000,
                'location_lng' => 106.816666,
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(7)->addHours(3),
                'capacity' => 100,
                'is_active' => true,
            ]
        );
    }
}

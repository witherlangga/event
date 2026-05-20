<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Event;

class EventLbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lbs_returns_nearby_events()
    {
        // create an organizer
        $organizer = User::factory()->create(['role' => 'organizer']);

        // nearby event (same coords as query)
        Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Nearby Event',
            'description' => 'Event within radius',
            'location_name' => 'Kampus Contoh',
            'location_address' => 'Jl. Contoh No.1',
            'location_lat' => -6.2,
            'location_lng' => 106.816666,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(3),
            'capacity' => 100,
            'is_active' => true,
        ]);

        // far event
        Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Far Event',
            'description' => 'Event outside radius',
            'location_name' => 'Nowhere',
            'location_address' => 'Ocean',
            'location_lat' => 0.0,
            'location_lng' => 0.0,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(3),
            'capacity' => 50,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/events?lat=-6.2&lng=106.8167&radius_km=10');

        $response->assertStatus(200);

        $json = $response->json();

        // assert nearby event exists and far event does not
        $this->assertTrue(collect($json)->contains(fn($e) => $e['title'] === 'Nearby Event'));
        $this->assertFalse(collect($json)->contains(fn($e) => $e['title'] === 'Far Event'));
    }
}

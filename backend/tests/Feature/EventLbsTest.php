<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;

class EventLbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lbs_returns_nearby_events()
    {
        Event::create([
            'title' => 'Konser Terdekat',
            'description' => 'Konser dalam radius pencarian',
            'location_name' => 'GBK Jakarta',
            'location_address' => 'Jl. Pintu Satu Senayan',
            'location_lat' => -6.2,
            'location_lng' => 106.816666,
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(3),
            'capacity' => 100,
            'is_active' => true,
        ]);

        Event::create([
            'title' => 'Konser Jauh',
            'description' => 'Konser di luar radius',
            'location_name' => 'Lokasi Jauh',
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

        $this->assertTrue(collect($json)->contains(fn ($e) => $e['title'] === 'Konser Terdekat'));
        $this->assertFalse(collect($json)->contains(fn ($e) => $e['title'] === 'Konser Jauh'));
    }
}

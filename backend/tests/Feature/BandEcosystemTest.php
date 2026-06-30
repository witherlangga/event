<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\BandMember;
use App\Models\Album;
use App\Models\NewsPost;

class BandEcosystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_band_members_endpoint(): void
    {
        BandMember::create([
            'name' => 'Alex Rivera',
            'role' => 'Vokal',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/band/members');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => 'Alex Rivera']);
    }

    public function test_public_albums_endpoint(): void
    {
        Album::create([
            'title' => 'Midnight Echoes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/band/albums');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['title' => 'Midnight Echoes']);
    }

    public function test_public_news_endpoint(): void
    {
        NewsPost::create([
            'title' => 'Tour 2026',
            'slug' => 'tour-2026',
            'body' => 'Konser resmi.',
            'published_at' => now()->subDay(),
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/band/news');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['title' => 'Tour 2026']);
    }
}

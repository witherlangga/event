<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Profile Saya');
        $response->assertSee('Test User');
    }

    public function test_authenticated_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => '081111111111',
            'bio' => 'Old bio',
            'password' => 'old-password',
        ]);

        $this->actingAs($user)->post('/profile', [
            'name' => 'New Name',
            'phone' => '082222222222',
            'bio' => 'Updated bio',
            'location_lat' => '-6.200000',
            'location_lng' => '106.816666',
        ])->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone' => '082222222222',
            'bio' => 'Updated bio',
        ]);

        $this->actingAs($user)->post('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('/profile');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }
}

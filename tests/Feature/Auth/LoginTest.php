<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_jeune_is_redirected_to_jeune_dashboard()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => true
        ]);

        $response = $this->actingAs($user)->get(route('jeune.dashboard'));
        $response->assertStatus(200);
    }

    public function test_mentor_is_redirected_to_mentor_dashboard()
    {
        $user = User::factory()->mentor()->create([
            'onboarding_completed' => true
        ]);

        $response = $this->actingAs($user)->get(route('mentor.dashboard'));
        $response->assertStatus(200);
    }

    public function test_organization_is_redirected_to_organization_dashboard()
    {
        $user = User::factory()->organization()->create();
        $org = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'contact_email' => $user->email
        ]);

        $user->update(['organization_id' => $org->id]);

        // We need to request the subdomain so ResolveOrganizationByDomain middleware works correctly
        $url = route('organization.dashboard');
        $url = str_replace('brillio.africa', 'test-org.brillio.africa', $url);

        $response = $this->actingAs($user)->get($url);

        $response->assertStatus(200);
    }

    public function test_archived_user_is_reactivated_on_login_attempt()
    {
        $user = User::factory()->archived()->create([
            'email' => 'archived@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertNotNull($user->archived_at);

        $response = $this->post(route('auth.jeune.login.submit'), [
            'email' => 'archived@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->archived_at);
        $this->assertTrue(auth()->check());
    }
}
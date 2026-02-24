<?php

namespace Tests\Feature\Organization;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->organization()->create();
        $this->organization = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'contact_email' => $this->admin->email,
            'subscription_plan' => 'pro' // Enable pro features for testing
        ]);

        $this->admin->update(['organization_id' => $this->organization->id]);
    }

    protected function getOrgUrl($routeName, $params = [])
    {
        $url = route($routeName, $params);
        return str_replace('brillio.africa', 'test-org.brillio.africa', $url);
    }

    public function test_organization_admin_can_view_dashboard()
    {
        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('organization');
    }

    public function test_organization_admin_can_invite_user()
    {
        $response = $this->actingAs($this->admin)->post($this->getOrgUrl('organization.invitations.store'), [
            'invited_emails' => 'new-user@example.com',
            'role' => 'jeune',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('organization_invitations', [
            'invited_email' => 'new-user@example.com',
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);
    }

    public function test_organization_admin_can_view_sponsored_users()
    {
        $sponsoredUser = User::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.users.index'));

        $response->assertStatus(200);
        $response->assertSee($sponsoredUser->first_name);
    }
}
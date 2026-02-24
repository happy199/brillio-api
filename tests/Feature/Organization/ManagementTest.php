<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use App\Models\User;
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
            'subscription_plan' => 'pro', // Enable pro features for testing
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
            'status' => 'pending',
        ]);
    }

    public function test_organization_admin_can_view_sponsored_users()
    {
        $sponsoredUser = User::factory()->create(['sponsored_by_organization_id' => $this->organization->id]);
        $sponsoredUser->organizations()->attach($this->organization->id);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.users.index'));

        $response->assertStatus(200);
        $response->assertSee($sponsoredUser->name);
    }

    public function test_enterprise_organization_can_access_team_management()
    {
        $this->organization->update(['subscription_plan' => 'enterprise']);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.team.index'));

        $response->assertStatus(200);
    }

    public function test_pro_organization_cannot_access_team_management()
    {
        $this->organization->update(['subscription_plan' => 'pro']);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.team.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('organization.subscriptions.index'));
    }

    public function test_enterprise_organization_can_create_team_member()
    {
        $this->organization->update(['subscription_plan' => 'enterprise']);

        $response = $this->actingAs($this->admin)->post($this->getOrgUrl('organization.team.store'), [
            'name' => 'John Viewer',
            'email' => 'viewer@example.com',
            'role' => 'viewer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'viewer@example.com',
            'organization_role' => 'viewer',
        ]);

        $user = User::where('email', 'viewer@example.com')->first();
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'role' => 'viewer',
        ]);
    }

    public function test_team_members_are_excluded_from_youth_list()
    {
        // 1. Create a youth linked to the org
        $sponsoredUser = User::factory()->create(['sponsored_by_organization_id' => $this->organization->id]);
        $sponsoredUser->organizations()->attach($this->organization->id);

        // 2. Create another admin/viewer in the same org
        $teamMember = User::factory()->organization()->create(['organization_id' => $this->organization->id]);
        $teamMember->organizations()->attach($this->organization->id, ['role' => 'viewer']);

        // 3. Request youth list
        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.users.index'));

        $response->assertStatus(200);
        $response->assertSee($sponsoredUser->name);

        // Assert that the count only reflects the youth
        $response->assertSee('Liste des 1 jeunes inscrits');

        // Ensure the team member is not in the list
        $response->assertDontSee($teamMember->name);
    }
}

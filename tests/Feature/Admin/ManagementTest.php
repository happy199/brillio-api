<?php

namespace Tests\Feature\Admin;

use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_can_list_users()
    {
        User::factory()->count(5)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_admin_can_block_and_unblock_user()
    {
        $user = User::factory()->create();

        // Block
        $response = $this->actingAs($this->admin)->post(route('admin.users.block', $user), [
            'reason' => 'Test reason',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_blocked' => true,
            'blocked_reason' => 'Test reason',
        ]);

        // Unblock
        $response = $this->actingAs($this->admin)->post(route('admin.users.unblock', $user));
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_blocked' => false,
        ]);
    }

    public function test_admin_can_toggle_admin_status()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.toggle-admin', $user));
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_list_mentors()
    {
        MentorProfile::factory()->count(3)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.mentors.index'));
        $response->assertStatus(200);
        $response->assertViewHas('mentors');
    }

    public function test_admin_can_approve_mentor()
    {
        $mentorUser = User::factory()->mentor()->create();
        $mentorProfile = MentorProfile::factory()->create([
            'user_id' => $mentorUser->id,
            'is_validated' => false,
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.mentors.approve', $mentorProfile));
        $response->assertRedirect();

        $mentorProfile->refresh();
        $this->assertTrue($mentorProfile->is_validated);
        $this->assertTrue($mentorProfile->is_published);
    }

    public function test_admin_can_toggle_mentor_publication()
    {
        $mentorProfile = MentorProfile::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->admin)->patch(route('admin.mentors.toggle-publish', $mentorProfile));
        $response->assertRedirect();

        $mentorProfile->refresh();
        $this->assertFalse($mentorProfile->is_published);
    }

    public function test_admin_can_list_organizations()
    {
        Organization::factory()->count(2)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.organizations.index'));
        $response->assertStatus(200);
        $response->assertViewHas('organizations');
    }

    public function test_admin_can_moderate_specializations()
    {
        $spec = Specialization::create([
            'name' => 'Pending Spec',
            'slug' => 'pending-spec',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.specializations.approve', $spec));
        $response->assertRedirect();

        $this->assertDatabaseHas('specializations', [
            'id' => $spec->id,
            'status' => 'active',
        ]);
    }
}
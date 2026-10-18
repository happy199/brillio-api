<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalPaginationQueryStringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_pagination_preserves_filter_query_strings()
    {
        $admin = User::factory()->admin()->create();

        // Create 30 young users
        for ($i = 0; $i < 30; $i++) {
            User::factory()->create([
                'name' => "Jeune Utilisateur {$i}",
                'user_type' => User::TYPE_JEUNE,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'user_type' => 'jeune',
            'search' => 'Utilisateur',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('user_type=jeune', $content);
        $this->assertStringContainsString('search=Utilisateur', $content);
        $this->assertStringContainsString('page=2', $content);
    }

    public function test_jeune_mentors_pagination_preserves_filter_query_strings()
    {
        $jeune = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => true,
        ]);

        for ($i = 0; $i < 20; $i++) {
            $mentor = User::factory()->create([
                'name' => "Mentor Test {$i}",
                'user_type' => User::TYPE_MENTOR,
            ]);
            MentorProfile::factory()->create([
                'user_id' => $mentor->id,
                'is_published' => true,
                'is_validated' => true,
                'specialization' => 'Technologie & IT',
            ]);
        }

        $response = $this->actingAs($jeune)->get(route('jeune.mentors', [
            'search' => 'Mentor',
            'specialization' => 'Technologie & IT',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('search=Mentor', $content);
        $this->assertStringContainsString('page=2', $content);
    }

    public function test_admin_contact_messages_pagination_preserves_query_strings()
    {
        $admin = User::factory()->admin()->create();

        for ($i = 0; $i < 25; $i++) {
            ContactMessage::create([
                'name' => "Expediteur {$i}",
                'email' => "user{$i}@example.com",
                'subject' => 'Support Demande',
                'message' => 'Message de test',
                'status' => 'new',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.index', [
            'status' => 'new',
            'search' => 'Demande',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('status=new', $content);
        $this->assertStringContainsString('search=Demande', $content);
        $this->assertStringContainsString('page=2', $content);
    }

    public function test_organization_users_pagination_preserves_query_strings()
    {
        $org = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'subscription_plan' => 'pro',
        ]);
        $orgAdmin = User::factory()->create([
            'user_type' => User::TYPE_ORGANIZATION,
            'organization_id' => $org->id,
            'organization_role' => 'admin',
        ]);

        $org->users()->attach($orgAdmin->id, ['role' => 'admin']);

        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create([
                'name' => "Talent {$i}",
                'user_type' => User::TYPE_JEUNE,
                'sponsored_by_organization_id' => $org->id,
            ]);
            $org->users()->attach($user->id, ['role' => 'jeune']);
        }

        $response = $this->actingAs($orgAdmin)->get(route('organization.users.index', ['search' => 'Talent']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('search=Talent', $content);
        $this->assertStringContainsString('page=2', $content);
    }
}

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

    private const PAGE_TWO_QUERY = 'page=2';

    private function assertPaginationPreserved($user, string $routeName, array $params, array $expectedStrings): void
    {
        $response = $this->actingAs($user)->get(route($routeName, $params));
        $response->assertStatus(200);
        $content = $response->getContent();
        foreach ($expectedStrings as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
        $this->assertStringContainsString(self::PAGE_TWO_QUERY, $content);
    }

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

        $this->assertPaginationPreserved($admin, 'admin.users.index', [
            'user_type' => 'jeune',
            'search' => 'Utilisateur',
        ], ['user_type=jeune', 'search=Utilisateur']);
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

        $this->assertPaginationPreserved($jeune, 'jeune.mentors', [
            'search' => 'Mentor',
            'specialization' => 'Technologie & IT',
        ], ['search=Mentor']);
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

        $this->assertPaginationPreserved($admin, 'admin.contact-messages.index', [
            'status' => 'new',
            'search' => 'Demande',
        ], ['status=new', 'search=Demande']);
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

        $this->assertPaginationPreserved($orgAdmin, 'organization.users.index', [
            'search' => 'Talent',
        ], ['search=Talent']);
    }

    public function test_multi_page_navigation_preserves_filters_from_page_two_to_page_three()
    {
        $admin = User::factory()->admin()->create();

        // Create 80 young users to have 4 pages (25 per page in UserController)
        for ($i = 0; $i < 80; $i++) {
            User::factory()->create([
                'name' => "Jeune Multipage {$i}",
                'user_type' => User::TYPE_JEUNE,
            ]);
        }

        // 1. Navigate to Page 2 with filters
        $responsePage2 = $this->actingAs($admin)->get(route('admin.users.index', [
            'user_type' => 'jeune',
            'search' => 'Multipage',
            'page' => 2,
        ]));
        $responsePage2->assertStatus(200);
        $contentPage2 = $responsePage2->getContent();

        $this->assertStringContainsString('user_type=jeune', $contentPage2);
        $this->assertStringContainsString('search=Multipage', $contentPage2);
        $this->assertStringContainsString('page=3', $contentPage2);

        // 2. Navigate to Page 3 with filters
        $responsePage3 = $this->actingAs($admin)->get(route('admin.users.index', [
            'user_type' => 'jeune',
            'search' => 'Multipage',
            'page' => 3,
        ]));
        $responsePage3->assertStatus(200);
        $contentPage3 = $responsePage3->getContent();

        $this->assertStringContainsString('user_type=jeune', $contentPage3);
        $this->assertStringContainsString('search=Multipage', $contentPage3);
        $this->assertStringContainsString('page=4', $contentPage3);
    }
}

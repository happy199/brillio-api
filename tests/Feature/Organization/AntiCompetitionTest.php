<?php

namespace Tests\Feature\Organization;

use App\Models\Establishment;
use App\Models\Organization;
use App\Models\PersonalityTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntiCompetitionTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_DESCRIPTION = 'Description test';

    private const RECOMMENDED_TRAININGS_TEXT = 'Formations recommandées';

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->organization()->create();
        $this->organization = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'contact_email' => $this->admin->email,
            'subscription_plan' => 'pro',
            'anti_competition_enabled' => false,
        ]);

        $this->admin->update(['organization_id' => $this->organization->id]);
    }

    protected function getOrgUrl(string $routeName, array $params = []): string
    {
        $url = route($routeName, $params);

        return str_replace('brillio.africa', 'test-org.brillio.africa', $url);
    }

    public function test_direct_model_update(): void
    {
        $this->organization->update(['anti_competition_enabled' => true]);
        $this->assertTrue($this->organization->fresh()->anti_competition_enabled);

        $this->organization->update(['anti_competition_enabled' => false]);
        $this->assertFalse($this->organization->fresh()->anti_competition_enabled);
    }

    public function test_organization_admin_can_enable_and_disable_anti_competition_toggle(): void
    {
        $this->actingAs($this->admin);

        // 1. Enable toggle
        $response = $this->put($this->getOrgUrl('organization.profile.update'), [
            'name' => $this->organization->name,
            'contact_email' => $this->organization->contact_email,
            'anti_competition_enabled' => '1',
        ]);

        $response->assertRedirect();
        $this->organization->refresh();
        $this->assertTrue($this->organization->anti_competition_enabled);

        // 2. Disable toggle
        $response = $this->put($this->getOrgUrl('organization.profile.update'), [
            'name' => $this->organization->name,
            'contact_email' => $this->organization->contact_email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->organization->refresh();
        $this->assertFalse($this->organization->anti_competition_enabled);
    }

    public function test_youth_has_anti_competition_restriction_when_organization_enables_it(): void
    {
        $youth = User::factory()->create();
        $youth->organizations()->attach($this->organization->id);

        $this->assertFalse($youth->hasAntiCompetitionRestriction());

        $this->organization->update(['anti_competition_enabled' => true]);
        $youth->refresh();

        $this->assertTrue($youth->hasAntiCompetitionRestriction());
    }

    private function createYouthWithPersonality(array $attributes = [], string $type = 'ENFP', string $label = 'Inspirateur'): User
    {
        $youth = User::factory()->create(array_merge(['user_type' => 'jeune'], $attributes));
        $youth->organizations()->attach($this->organization->id);

        PersonalityTest::create([
            'user_id' => $youth->id,
            'personality_type' => $type,
            'personality_label' => $label,
            'personality_description' => self::TEST_DESCRIPTION,
            'traits_scores' => [],
            'raw_responses' => [],
            'completed_at' => now(),
            'is_current' => true,
        ]);

        return $youth;
    }

    private function assertRecommendationVisibilityToggled(User $youth, string $routeName): void
    {
        $this->actingAs($youth)->get(route($routeName))
            ->assertOk()
            ->assertSee(self::RECOMMENDED_TRAININGS_TEXT);

        $this->organization->update(['anti_competition_enabled' => true]);

        $this->actingAs($youth)->get(route($routeName))
            ->assertOk()
            ->assertDontSee(self::RECOMMENDED_TRAININGS_TEXT);
    }

    public function test_youth_in_restricted_organization_receives_empty_recommended_establishments(): void
    {
        $youth = $this->createYouthWithPersonality([], 'ENFJ', 'Protagoniste');

        Establishment::create([
            'name' => 'African Leadership University',
            'description' => 'Excellence school',
            'mbti_types' => ['ENFJ'],
            'is_published' => true,
        ]);

        // When anti-competition is disabled
        $resNotRestricted = $this->actingAs($youth)->get(route('jeune.establishments.recommended'));
        $resNotRestricted->assertOk();
        $resNotRestricted->assertJsonFragment(['name' => 'African Leadership University']);

        // When anti-competition is enabled
        $this->organization->update(['anti_competition_enabled' => true]);

        $resRestricted = $this->actingAs($youth)->get(route('jeune.establishments.recommended'));
        $resRestricted->assertOk();
        $resRestricted->assertJson(['establishments' => []]);
    }

    public function test_api_v2_recommended_establishments_returns_empty_when_restricted(): void
    {
        $this->organization->update(['anti_competition_enabled' => true]);
        $youth = $this->createYouthWithPersonality([], 'INTJ', 'Architecte');

        Establishment::create([
            'name' => 'Tech Institute',
            'description' => 'Tech institute',
            'mbti_types' => ['INTJ'],
            'is_published' => true,
        ]);

        $response = $this->actingAs($youth)->getJson('/api/v2/establishments/recommended');
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'establishments' => [],
            ],
        ]);
    }

    public function test_personality_page_hides_recommendations_for_restricted_youth(): void
    {
        $youth = $this->createYouthWithPersonality();
        $this->assertRecommendationVisibilityToggled($youth, 'jeune.personality');
    }

    public function test_dashboard_hides_recommendations_for_restricted_youth(): void
    {
        $youth = $this->createYouthWithPersonality(['onboarding_completed' => true]);
        $this->assertRecommendationVisibilityToggled($youth, 'jeune.dashboard');
    }
}

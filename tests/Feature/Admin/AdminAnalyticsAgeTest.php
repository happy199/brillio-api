<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsAgeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_user_model_age_and_birth_date_accessors()
    {
        // User with date_of_birth
        $user = User::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d'),
        ]);

        $this->assertEquals(20, $user->age);
        $this->assertNotNull($user->birth_date);
        $this->assertEquals($user->date_of_birth->format('Y-m-d'), $user->birth_date->format('Y-m-d'));

        // User without date_of_birth
        $emptyUser = User::factory()->create([
            'date_of_birth' => null,
        ]);
        $this->assertNull($emptyUser->age);
        $this->assertNull($emptyUser->birth_date);

        // User with birth_date in onboarding_data only (fallback)
        $onboardingUser = User::factory()->create([
            'date_of_birth' => null,
            'onboarding_data' => [
                'birth_date' => Carbon::now()->subYears(19)->format('Y-m-d'),
            ],
        ]);
        $this->assertEquals(19, $onboardingUser->age);
        $this->assertNotNull($onboardingUser->birth_date);
    }

    public function test_user_details_displays_birth_date_and_age()
    {
        // 1. User with date_of_birth
        $userWithDob = User::factory()->create([
            'user_type' => 'jeune',
            'date_of_birth' => Carbon::parse('2002-05-15'),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $userWithDob));
        $response->assertStatus(200);
        $response->assertSee('15/05/2002');
        $response->assertSee($userWithDob->age.' ans');

        // 2. User without date_of_birth
        $userWithoutDob = User::factory()->create([
            'user_type' => 'jeune',
            'date_of_birth' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $userWithoutDob));
        $response->assertStatus(200);
        $response->assertSee('Non renseignée');
    }

    public function test_analytics_dashboard_contains_age_distribution_and_chart()
    {
        // Create youth users in different age categories
        $ages = [14, 16, 20, 24, 28, 35, null];
        foreach ($ages as $age) {
            User::factory()->create([
                'user_type' => 'jeune',
                'is_admin' => false,
                'date_of_birth' => $age !== null ? Carbon::now()->subYears($age)->toDateString() : null,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.analytics.index', ['preset' => 'all']));
        $response->assertStatus(200);
        $response->assertViewHas('stats');

        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('youth_engagement', $stats);
        $this->assertArrayHasKey('age_distribution', $stats['youth_engagement']);

        $ageDist = $stats['youth_engagement']['age_distribution'];
        $this->assertEquals(1, $ageDist['under_15']);
        $this->assertEquals(1, $ageDist['15_17']);
        $this->assertEquals(1, $ageDist['18_21']);
        $this->assertEquals(1, $ageDist['22_25']);
        $this->assertEquals(1, $ageDist['26_30']);
        $this->assertEquals(1, $ageDist['over_30']);
        $this->assertEquals(1, $ageDist['non_renseigne']);

        // Assert chart canvas exists in the rendered HTML
        $response->assertSee('id="ageChart"', false);
    }
}

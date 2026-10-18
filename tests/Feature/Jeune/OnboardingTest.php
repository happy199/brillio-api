<?php

namespace Tests\Feature\Jeune;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_SENEGAL_PHONE = '771234567';

    /**
     * Test the index page loads for uncompleted onboarding user
     */
    public function test_onboarding_page_loads_for_jeune()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.onboarding'));
        $response->assertStatus(200);
        $response->assertSee('Informations personnelles');
        $response->assertSee('Numéro de téléphone');
    }

    /**
     * Test successful onboarding completion with valid phone numbers (Senegal)
     */
    public function test_complete_onboarding_success_senegal()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => '2005-05-12',
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => self::VALID_SENEGAL_PHONE, // Valid Senegal: 9 digits local
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertRedirect(route('jeune.dashboard'));
        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
        $this->assertEquals('+221771234567', $user->phone); // E.164
    }

    /**
     * Test successful onboarding with Benin 10 digit conversion (auto-prepend 0)
     */
    public function test_complete_onboarding_success_benin_auto_prepend()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => '2005-05-12',
            'country' => 'Benin',
            'city' => 'Cotonou',
            'phone' => '102030405', // 9 digits without leading 0 starting with 1 -> auto-prepends 0 -> 0102030405
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertRedirect(route('jeune.dashboard'));
        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
        $this->assertEquals('+2290102030405', $user->phone);
    }

    /**
     * Test successful onboarding with Côte d'Ivoire 10 digit conversion (auto-prepend 0)
     */
    public function test_complete_onboarding_success_cote_divoire_auto_prepend()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => '2005-05-12',
            'country' => 'Cote d\'Ivoire',
            'city' => 'Abidjan',
            'phone' => '708091011', // 9 digits without leading 0 -> auto-prepends 0 -> 0708091011
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertRedirect(route('jeune.dashboard'));
        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
        $this->assertEquals('+2250708091011', $user->phone);
    }

    /**
     * Test validation fails for invalid phone number length
     */
    public function test_complete_onboarding_fails_invalid_phone_length()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => '2005-05-12',
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => '7712345', // Too short for Senegal (needs 9 digits)
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertSessionHasErrors('phone');
        $user->refresh();
        $this->assertFalse($user->onboarding_completed);
    }

    /**
     * Test validation fails when birth_date is missing
     */
    public function test_complete_onboarding_fails_missing_birth_date()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => self::VALID_SENEGAL_PHONE,
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertSessionHasErrors('birth_date');
        $user->refresh();
        $this->assertFalse($user->onboarding_completed);
    }

    /**
     * Test validation fails when user is under 10 years old
     */
    public function test_complete_onboarding_fails_when_under_10_years_old()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => now()->subYears(8)->format('Y-m-d'), // 8 years old
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => self::VALID_SENEGAL_PHONE,
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertSessionHasErrors('birth_date');
        $user->refresh();
        $this->assertFalse($user->onboarding_completed);
    }

    /**
     * Test validation succeeds when user is 10 years old or older
     */
    public function test_complete_onboarding_succeeds_when_10_years_old_or_older()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.onboarding.complete'), [
            'birth_date' => now()->subYears(10)->format('Y-m-d'), // Exactly 10 years old
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => self::VALID_SENEGAL_PHONE,
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertRedirect(route('jeune.dashboard'));
        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
    }
}

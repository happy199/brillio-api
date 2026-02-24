<?php

namespace Tests\Feature\Mentor;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_view_profile_page()
    {
        $user = User::factory()->mentor()->create([
            'onboarding_completed' => true,
        ]);
        MentorProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('mentor.profile'));

        $response->assertStatus(200);
    }

    public function test_mentor_can_update_profile()
    {
        $user = User::factory()->mentor()->create([
            'onboarding_completed' => true,
        ]);
        $profile = MentorProfile::factory()->create(['user_id' => $user->id]);
        $spec = \App\Models\Specialization::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->put(route('mentor.profile.update'), [
            'bio' => 'Ma nouvelle biographie de test',
            'current_position' => 'Expert Laravel',
            'years_of_experience' => 10,
            'specialization_id' => (string) $spec->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Ma nouvelle biographie de test', $profile->fresh()->bio);
    }
}

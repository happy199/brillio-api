<?php

namespace Tests\Feature\Mentor;

use App\Mail\OrganizationUnlinkedNotificationMail;
use App\Mail\UserUnlinkedConfirmationMail;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MentorOrganizationUnlinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_unlink_organization()
    {
        $response = $this->post(route('mentor.profile.unlink'));

        $response->assertRedirect('/rejoindre');
    }

    public function test_mentor_without_organization_gets_error()
    {
        $mentor = User::factory()->mentor()->create([
            'onboarding_completed' => true,
            'organization_id' => null,
            'sponsored_by_organization_id' => null,
        ]);
        MentorProfile::factory()->create(['user_id' => $mentor->id]);

        $response = $this->actingAs($mentor)->post(route('mentor.profile.unlink'));

        $response->assertSessionHas('error', 'Aucune organisation liée.');
    }

    public function test_mentor_can_unlink_organization_and_emails_are_dispatched()
    {
        Mail::fake();

        $organization = Organization::factory()->create([
            'contact_email' => 'contact@test-organization.org',
            'name' => 'Test Organization',
        ]);

        $mentor = User::factory()->mentor()->create([
            'name' => 'Jane Mentor',
            'email' => 'jane.mentor@example.com',
            'onboarding_completed' => true,
            'organization_id' => $organization->id,
            'sponsored_by_organization_id' => $organization->id,
        ]);
        MentorProfile::factory()->create(['user_id' => $mentor->id]);

        $organization->users()->attach($mentor->id);

        $response = $this->actingAs($mentor)->post(route('mentor.profile.unlink'));

        $response->assertSessionHas('success', 'La relation de mentorat avec l\'organisation a été rompue avec succès.');

        // Verify detachment
        $mentor->refresh();
        $this->assertNull($mentor->organization_id);
        $this->assertNull($mentor->sponsored_by_organization_id);
        $this->assertFalse($organization->fresh()->users->contains($mentor->id));

        // Verify emails
        Mail::assertSent(OrganizationUnlinkedNotificationMail::class, function ($mail) use ($mentor) {
            return $mail->hasTo('contact@test-organization.org')
                && $mail->role === 'Mentor'
                && $mail->user->id === $mentor->id;
        });

        Mail::assertSent(UserUnlinkedConfirmationMail::class, function ($mail) use ($mentor, $organization) {
            return $mail->hasTo('jane.mentor@example.com')
                && $mail->organization->id === $organization->id
                && $mail->user->id === $mentor->id;
        });
    }
}

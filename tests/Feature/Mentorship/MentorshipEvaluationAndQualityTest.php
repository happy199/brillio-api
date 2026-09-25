<?php

namespace Tests\Feature\Mentorship;

use App\Models\JeuneProfile;
use App\Models\MentoringSession;
use App\Models\MentoringSessionEvaluation;
use App\Models\MentorProfile;
use App\Models\Mentorship;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorshipEvaluationAndQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_session_is_automatically_flagged_as_first_session()
    {
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Première Séance Test',
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);
        $session->mentees()->attach($mentee->id, ['status' => 'accepted']);

        $this->assertTrue($session->is_first_session);

        // Second session should not be flagged as first session automatically
        $secondSession = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Deuxième Séance Test',
            'scheduled_at' => now()->addDays(5),
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);

        $this->assertFalse($secondSession->is_first_session);
    }

    public function test_jeune_can_evaluate_mentor_after_report_submission()
    {
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        JeuneProfile::create([
            'user_id' => $mentee->id,
            'is_public' => true,
        ]);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance terminée avec compte-rendu',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => 'completed',
            'report_content' => [
                'progress' => 'Bons progrès accomplis.',
                'obstacles' => 'Aucun.',
                'smart_goals' => 'Continuer ainsi.',
            ],
        ]);
        $session->mentees()->attach($mentee->id, ['status' => 'accepted']);

        $response = $this->actingAs($mentee)->post(route('jeune.sessions.evaluate', $session), [
            'rating' => 5,
            'comment' => 'Excellente séance de mentorat, conseils très pertinents !',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mentoring_session_evaluations', [
            'mentoring_session_id' => $session->id,
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentor->id,
            'rating' => 5,
            'comment' => 'Excellente séance de mentorat, conseils très pertinents !',
        ]);

        // Check Mentor Profile Rating calculation
        $profile = MentorProfile::create([
            'user_id' => $mentor->id,
            'bio' => 'Bio mentor',
            'current_position' => 'Senior Lead',
            'specialization' => 'tech',
            'is_published' => true,
        ]);

        $this->assertEquals(5.0, $profile->average_rating);
        $this->assertEquals(1, $profile->evaluations_count);
    }

    public function test_admin_can_view_and_evaluate_first_sessions()
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_JEUNE, 'is_admin' => true]);
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance à évaluer par admin',
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 45,
            'status' => 'completed',
            'is_first_session' => true,
        ]);
        $session->mentees()->attach($mentee->id, ['status' => 'accepted']);

        $response = $this->actingAs($admin)->get(route('admin.mentorship.evaluations'));
        $response->assertStatus(200);
        $response->assertSee('Évaluation des séances de mentorat');

        $responseDetail = $this->actingAs($admin)->get(route('admin.mentorship.evaluations.show', $session));
        $responseDetail->assertStatus(200);

        // Store observation
        $obsResponse = $this->actingAs($admin)->post(route('admin.mentorship.evaluations.observation', $session), [
            'admin_observation' => 'Très bon échange, objectifs clairs.',
            'admin_evaluation_status' => 'validated',
        ]);
        $obsResponse->assertRedirect();

        $this->assertDatabaseHas('mentoring_sessions', [
            'id' => $session->id,
            'admin_evaluation_status' => 'validated',
            'admin_observation' => 'Très bon échange, objectifs clairs.',
        ]);
    }

    public function test_admin_can_terminate_mentorship_relationship()
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_JEUNE, 'is_admin' => true]);
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $mentorship = Mentorship::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'status' => 'accepted',
        ]);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance problème',
            'scheduled_at' => now()->subHours(1),
            'duration_minutes' => 30,
            'status' => 'completed',
            'is_first_session' => true,
        ]);
        $session->mentees()->attach($mentee->id, ['status' => 'accepted']);

        $response = $this->actingAs($admin)->post(route('admin.mentorship.evaluations.stop-relationship', $session), [
            'mentee_id' => $mentee->id,
            'reason' => 'Comportement non professionnel du mentor.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('mentorships', [
            'id' => $mentorship->id,
            'status' => 'disconnected',
        ]);

        $this->assertDatabaseHas('mentoring_sessions', [
            'id' => $session->id,
            'admin_evaluation_status' => 'terminated',
        ]);
    }

    public function test_public_mentor_profile_displays_evaluations_and_ratings()
    {
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $profile = MentorProfile::create([
            'user_id' => $mentor->id,
            'bio' => 'Bio mentor public',
            'current_position' => 'CEO',
            'specialization' => 'Business',
            'is_published' => true,
        ]);

        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE, 'name' => 'Jean Menteer']);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance terminée',
            'scheduled_at' => now()->subDays(2),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        MentoringSessionEvaluation::create([
            'mentoring_session_id' => $session->id,
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentor->id,
            'rating' => 5,
            'comment' => 'Mentorat d une qualite exceptionnelle !',
        ]);

        $response = $this->get(route('public.mentor.profile', $profile));
        $response->assertStatus(200);
        $response->assertSee('Avis & Évaluations des jeunes');
        $response->assertSee('Jean Menteer');
        $response->assertSee('Mentorat d une qualite exceptionnelle !');
        $response->assertSee('5.0 / 5');
    }

    public function test_organization_can_view_evaluations_and_submit_session_report()
    {
        $orgUser = User::factory()->create(['user_type' => User::TYPE_ORGANIZATION]);
        $org = Organization::factory()->create([
            'subscription_plan' => Organization::PLAN_PRO,
        ]);
        $orgUser->organizations()->attach($org->id, ['role' => 'admin']);

        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'name' => 'Paul Participant',
            'sponsored_by_organization_id' => $org->id,
        ]);

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance organisée par entreprise',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => 'confirmed',
            'scheduled_by_organization_id' => $org->id,
        ]);
        $session->mentees()->attach($mentee->id, ['status' => 'accepted']);

        MentoringSessionEvaluation::create([
            'mentoring_session_id' => $session->id,
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentor->id,
            'rating' => 4,
            'comment' => 'Très enrichissant pour mon parcours.',
        ]);

        $response = $this->actingAs($orgUser)->get(route('organization.sessions.show', $session));
        $response->assertStatus(200);
        $response->assertSee('Évaluations &amp; Notes des participants', false);
        $response->assertSee('Paul Participant');
        $response->assertSee('Très enrichissant pour mon parcours.');

        // Organization submits report
        $reportResponse = $this->actingAs($orgUser)->put(route('organization.sessions.report.update', $session), [
            'progress' => 'Objectifs atteints.',
            'obstacles' => 'Aucun blocage majeur.',
            'smart_goals' => 'Prochaine étape définie.',
        ]);

        $reportResponse->assertRedirect();

        $session->refresh();
        $this->assertEquals('Objectifs atteints.', $session->report_content['progress']);
    }

    public function test_pagination_preserves_first_only_filter_and_calculates_single_first_session_per_pair()
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_JEUNE, 'is_admin' => true]);
        $mentor = User::factory()->create(['user_type' => User::TYPE_MENTOR]);
        $mentee = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        // Create 2 sessions for the same mentor and mentee
        $session1 = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance 1 (Première)',
            'scheduled_at' => now()->subDays(10),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);
        $session1->mentees()->attach($mentee->id, ['status' => 'accepted']);
        $session1->recalculateFirstSessionFlag();

        $session2 = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance 2 (Deuxième)',
            'scheduled_at' => now()->subDays(5),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);
        $session2->mentees()->attach($mentee->id, ['status' => 'accepted']);
        $session2->recalculateFirstSessionFlag();

        $this->assertTrue($session1->fresh()->is_first_session);
        $this->assertFalse($session2->fresh()->is_first_session);

        // Admin checks page 1 with default first_only=1
        $response = $this->actingAs($admin)->get(route('admin.mentorship.evaluations', ['first_only' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Séance 1 (Première)');
        $response->assertDontSee('Séance 2 (Deuxième)');
    }
}

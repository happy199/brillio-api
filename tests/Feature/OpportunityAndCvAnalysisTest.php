<?php

namespace Tests\Feature;

use App\Models\CvAnalysis;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpportunityAndCvAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private const MIME_PDF = 'application/pdf';

    private const STATUS_TRES_BIEN = 'Très bien';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_public_opportunities_page_is_accessible()
    {
        $response = $this->get(route('public.opportunities'));

        $response->assertStatus(200);
        $response->assertSee('Opportunités');
        $response->assertSee('Votre CV est-il assez percutant', false);
        $response->assertSee('Glissez-déposez votre CV ici');
    }

    public function test_guest_can_upload_and_analyze_cv()
    {
        $file = UploadedFile::fake()->create('CV_Fatoumata_Traore_UXUI.pdf', 150, self::MIME_PDF);

        $response = $this->post(route('public.opportunities.analyze'), [
            'cv_file' => $file,
        ]);

        $this->assertDatabaseCount('cv_analyses', 1);

        $analysis = CvAnalysis::first();
        $this->assertNotNull($analysis);
        $this->assertNull($analysis->user_id);
        $this->assertFalse($analysis->is_claimed);
        $this->assertGreaterThanOrEqual(10, $analysis->global_score);
        $this->assertNotNull($analysis->guest_token);

        $response->assertRedirect(route('public.opportunities.score', ['token' => $analysis->guest_token]));
        $this->assertEquals($analysis->guest_token, session('pending_cv_token'));
    }

    public function test_guest_cannot_upload_invalid_file_extension()
    {
        $file = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->post(route('public.opportunities.analyze'), [
            'cv_file' => $file,
        ]);

        $response->assertSessionHasErrors('cv_file');
        $this->assertDatabaseCount('cv_analyses', 0);
    }

    public function test_guest_score_page_shows_locked_notice_and_registration_cta()
    {
        $analysis = CvAnalysis::create([
            'guest_token' => 'abcdef1234567890abcdef1234567890',
            'original_filename' => 'CV_Fatoumata_Traore.pdf',
            'file_path' => 'cv_analyses/guests/fake.pdf',
            'file_size' => 10240,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Fatoumata TRAORÉ',
            'candidate_title' => 'UX/UI Designer',
            'global_score' => 66,
            'status_label' => self::STATUS_TRES_BIEN,
            'is_claimed' => false,
        ]);

        $response = $this->get(route('public.opportunities.score', ['token' => $analysis->guest_token]));

        $response->assertStatus(200);
        $response->assertSee('Votre Score ATS');
        $response->assertSee('66');
        $response->assertSee('Débloquer le rapport complet');
        $response->assertSee('Votre CV, deux versions');
    }

    public function test_registered_user_claims_pending_cv_analysis()
    {
        $analysis = CvAnalysis::create([
            'guest_token' => 'token_for_claim_test_12345678901234567890',
            'original_filename' => 'CV_Test.pdf',
            'file_path' => 'cv_analyses/guests/test.pdf',
            'file_size' => 5000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Jean Dupont',
            'candidate_title' => 'Data Analyst',
            'global_score' => 74,
            'status_label' => self::STATUS_TRES_BIEN,
            'is_claimed' => false,
        ]);

        $response = $this->withSession(['pending_cv_token' => $analysis->guest_token])
            ->post(route('auth.jeune.register.submit'), [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@testbrillio.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $user = User::where('email', 'jean.dupont@testbrillio.com')->first();
        $this->assertNotNull($user);

        $analysis->refresh();
        $this->assertEquals($user->id, $analysis->user_id);
        $this->assertTrue($analysis->is_claimed);

        // L'utilisateur doit être redirigé vers l'onboarding s'il n'est pas encore complété
        $response->assertRedirect(route('jeune.onboarding'));
    }

    public function test_authenticated_jeune_can_access_opportunities_tabs_and_unlocked_cv()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'auth_token_test_12345678901234567890',
            'original_filename' => 'CV_Jean_Dev.pdf',
            'file_path' => 'cv_analyses/users/'.$user->id.'/cv.pdf',
            'file_size' => 8000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Jean Développeur',
            'candidate_title' => 'Fullstack Laravel',
            'global_score' => 82,
            'status_label' => self::STATUS_TRES_BIEN,
            'summary' => 'Excellent profil technique.',
            'criteria_scores' => [
                'structure' => 80,
                'clarite' => 85,
                'experiences' => 80,
                'competences' => 85,
                'impact' => 80,
            ],
            'strengths' => ['Stack technique solide'],
            'improvements' => ['Ajouter des métriques'],
            'recommendations' => ['Participer à des hackathons'],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.documents', ['tab' => 'cv']));

        $response->assertStatus(200);
        $response->assertSee('Opportunités');
        $response->assertSee('Diagnostic Débloqué');
        $response->assertSee('82');
        $response->assertSee('Score Career : Très bien');
        $response->assertSee('Stack technique solide');
        $response->assertSee('Détail des 5 piliers', false);
    }

    public function test_authenticated_jeune_can_upload_and_analyze_new_cv()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $file = UploadedFile::fake()->create('CV_Updated_Version.docx', 120, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($user)->post(route('jeune.cv.analyze'), [
            'cv_file' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cv_analyses', [
            'user_id' => $user->id,
            'original_filename' => 'CV_Updated_Version.docx',
            'is_claimed' => true,
        ]);
    }

    public function test_public_navbar_displays_outils_with_new_tag_and_submenus()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Outils');
        $response->assertSee('New');
        $response->assertSee('Analyse CV');
        $response->assertSee('Ressources');
    }

    public function test_authenticated_jeune_navigation_has_opportunities_and_outils()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.opportunities'));

        $response->assertStatus(200);
        $response->assertSee('Opportunités');
        $response->assertSee('Outils');
        $response->assertSee('New');
        $response->assertSee('Emploi');
        $response->assertSee('Formation');
    }

    public function test_outils_page_displays_cv_first_ressources_and_documents()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.outils'));

        $response->assertStatus(200);
        $response->assertSee('Outils');
        $response->assertSee('CV');
        $response->assertSee('Ressources');
        $response->assertSee('Documents');
    }

    public function test_cv_action_redirects_to_wallet_when_insufficient_credits()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 0,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_insufficient_test',
            'original_filename' => 'Mon_CV.pdf',
            'file_path' => 'cv_analyses/test.pdf',
            'file_size' => 5000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Adama Traore',
            'candidate_title' => 'Développeur Backend',
            'global_score' => 78,
            'status_label' => self::STATUS_TRES_BIEN,
            'summary' => 'Profil de développeur backend.',
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'copy',
            'cv_id' => $analysis->id,
        ]);

        $response->assertStatus(402);
        $response->assertJson([
            'success' => false,
            'redirect_to_wallet' => true,
            'wallet_url' => route('jeune.wallet.index'),
        ]);
    }

    public function test_cv_action_deducts_credits_and_returns_data_when_sufficient_credits()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 10,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_sufficient_test',
            'original_filename' => 'Mon_CV_Pro.pdf',
            'file_path' => 'cv_analyses/test.pdf',
            'file_size' => 5000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Adama Traore',
            'candidate_title' => 'Développeur Backend',
            'global_score' => 85,
            'status_label' => 'Excellent',
            'summary' => 'Profil complet avec 5 ans d\'expérience.',
            'parsed_content' => [
                'experiences' => [
                    [
                        'title' => 'Backend Engineer',
                        'company' => 'Brillio Tech',
                        'period' => '2022 - 2024',
                        'description' => 'Développement d\'APIs haute performance.',
                    ],
                ],
                'skills' => ['PHP', 'Laravel', 'Docker'],
            ],
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'copy',
            'cv_id' => $analysis->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'copy',
            'cost' => 1,
            'remaining_balance' => 9,
        ]);

        $this->assertEquals(9, $user->fresh()->credits_balance);
        $this->assertStringContainsString('ADAMA TRAORE', $response->json('cv_text'));
        $this->assertStringContainsString('Backend Engineer', $response->json('cv_text'));
    }

    public function test_cv_action_is_free_when_cost_set_to_zero()
    {
        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_cv_download'],
            ['value' => 0]
        );

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 0,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_free_test',
            'original_filename' => 'Mon_CV_Free.pdf',
            'file_path' => 'cv_analyses/test.pdf',
            'file_size' => 5000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => 'Fatou Sylla',
            'candidate_title' => 'Chef de Projet',
            'global_score' => 80,
            'status_label' => self::STATUS_TRES_BIEN,
            'summary' => 'Chef de projet certifiée.',
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'download',
            'cv_id' => $analysis->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'download',
            'cost' => 0,
            'remaining_balance' => 0,
        ]);

        $this->assertEquals(0, $user->fresh()->credits_balance);
    }
}

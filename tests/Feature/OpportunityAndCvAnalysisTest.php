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

    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const TEST_DEV_OPS_TITLE = 'Senior DevOps Engineer';

    private const TEST_CANDIDATE_NAME = 'Moussa Diallo';

    private const TEST_CV_PATH_PDF = 'cv_analyses/test.pdf';

    private const TEXT_OPPORTUNITES = 'Opportunités';

    private const STATUS_TRES_BIEN = 'Très bien';

    private const EXTENSION_DOCX = '.docx';

    private const DOCX_DOCUMENT_XML = 'word/document.xml';

    private const TEST_CUSTOM_VALUE_REQUESTS = '15 000';

    private const TEST_CUSTOM_VALUE_YEARS = '3 années';

    private function extractDocxXmlContent($streamedResponse, string $tempPrefix = 'test_docx_'): string
    {
        $tempDocx = tempnam(sys_get_temp_dir(), $tempPrefix).self::EXTENSION_DOCX;
        file_put_contents($tempDocx, $streamedResponse->streamedContent());

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($tempDocx));
        $xml = (string) $zip->getFromName(self::DOCX_DOCUMENT_XML);
        $zip->close();
        unlink($tempDocx);

        return $xml;
    }

    private function createCvAnalysisFixture(User $user, array $overrides = []): CvAnalysis
    {
        return CvAnalysis::create(array_merge([
            'user_id' => $user->id,
            'guest_token' => 'token_'.uniqid(),
            'original_filename' => 'CV_Candidat.pdf',
            'file_path' => 'cv_analyses/test.pdf',
            'file_size' => 12345,
            'mime_type' => self::MIME_PDF,
            'status' => 'completed',
            'candidate_name' => 'Amadou Coulibaly',
            'candidate_title' => 'Développeur Web & Mobile Full-Stack',
            'candidate_contact' => [
                'email' => 'amadou.coulibaly@email.com',
                'phone' => '+225 07 00 00 00 00',
                'location' => "Abidjan, Côte d'Ivoire",
            ],
            'global_score' => 82,
            'status_label' => self::STATUS_TRES_BIEN,
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ], $overrides));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_public_opportunities_page_is_accessible()
    {
        $response = $this->get(route('public.opportunities'));

        $response->assertStatus(200);
        $this->assertEquals(url('/analysemoncv'), route('public.opportunities'));
        $response->assertSee('Analyse mon CV');
        $response->assertSee('Votre CV est-il assez percutant', false);
        $response->assertSee('Glissez-déposez votre CV ici');

        // Test 301 redirect from /opportunites
        $redirectResponse = $this->get('/opportunites');
        $redirectResponse->assertStatus(301);
        $redirectResponse->assertRedirect('/analysemoncv');
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
        $response->assertSee(self::TEXT_OPPORTUNITES);
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

        $file = UploadedFile::fake()->create('CV_Updated_Version.docx', 120, self::MIME_DOCX);

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
        $response->assertSee(self::TEXT_OPPORTUNITES);
        $response->assertSee('Soon');
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
            'file_path' => self::TEST_CV_PATH_PDF,
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
            'file_path' => self::TEST_CV_PATH_PDF,
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
            'file_path' => self::TEST_CV_PATH_PDF,
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

    public function test_cv_action_charges_tiered_credits_for_selected_template()
    {
        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_cv_template_3'],
            ['value' => 3]
        );

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 5,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_tier_test',
            'original_filename' => 'Moussa_Diallo_CV.pdf',
            'file_path' => 'cv_analyses/moussa.pdf',
            'file_size' => 12000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'candidate_title' => self::TEST_DEV_OPS_TITLE,
            'global_score' => 88,
            'status_label' => 'Excellent',
            'summary' => 'DevOps engineer with 9 years of experience.',
            'parsed_content' => [
                'experiences' => [
                    'Lead DevOps Engineer - Technology company (4 years) : Led the full migration.',
                ],
                'skills' => ['Kubernetes', 'Docker', 'Terraform'],
            ],
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'download',
            'cv_id' => $analysis->id,
            'template' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'download',
            'template' => 3,
            'cost' => 3,
            'remaining_balance' => 2,
        ]);

        $this->assertEquals(2, $user->fresh()->credits_balance);
    }

    public function test_cv_view_original_serves_uploaded_file()
    {
        Storage::disk('public')->put('cv_analyses/test_doc.pdf', 'dummy content');

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_view_test',
            'original_filename' => 'mon_cv.pdf',
            'file_path' => 'cv_analyses/test_doc.pdf',
            'file_size' => 100,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'global_score' => 80,
            'status_label' => self::STATUS_TRES_BIEN,
            'summary' => 'DevOps',
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.cv.view-original', $analysis->id));
        $response->assertStatus(200);
    }

    public function test_cv_detects_language_and_extracts_real_profile_summary()
    {
        $rawCvEnglish = <<<'TXT'
MOUSSA DIALLO
Senior DevOps Engineer — Cloud Infrastructure & Automation
Abidjan, Côte d'Ivoire | +225 07 00 00 00 00 | moussa@example.com

PROFESSIONAL SUMMARY
DevOps engineer with 9 years of experience, including 4 years as Lead DevOps. Specialized in migrating manually managed infrastructure to fully automated environments (Kubernetes, Terraform, CI/CD).

CORE SKILLS
Orchestration : Kubernetes, Docker
Infrastructure as Code : Terraform, Ansible

PROFESSIONAL EXPERIENCE
Lead DevOps Engineer — Technology company, Abidjan
4 years — current role
Led the full migration of a manually managed infrastructure to a fully automated setup.

EDUCATION
Engineering Degree in Computer Science — Engineering School (2015)

CERTIFICATIONS
Certified Kubernetes Administrator (CKA)
AWS Certified Solutions Architect – Associate

LANGUAGES
French (native) — English (fluent)
TXT;

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 5,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_moussa_test',
            'original_filename' => 'Moussa_Diallo_CV.pdf',
            'file_path' => 'cv_analyses/moussa.pdf',
            'file_size' => 12000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'candidate_title' => self::TEST_DEV_OPS_TITLE,
            'global_score' => 88,
            'status_label' => 'Excellent',
            // Notice: the AI critique is stored in summary
            'summary' => 'Ce CV est très bien structuré et optimisé pour les ATS, avec des rubriques claires.',
            'parsed_content' => [
                'raw_text' => $rawCvEnglish,
                'experiences' => [
                    'Lead DevOps Engineer — Technology company (4 years) : Led the full migration.',
                ],
                'education' => [
                    'Engineering Degree in Computer Science — Engineering School (2015)',
                ],
                'skills' => ['Kubernetes', 'Docker', 'Terraform', 'Ansible'],
            ],
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $norm = $analysis->normalized_cv_data;

        // Verify language detection
        $this->assertTrue($norm['is_english']);
        $this->assertEquals('PROFESSIONAL SUMMARY', $norm['labels']['profile']);
        $this->assertEquals('PROFESSIONAL EXPERIENCE', $norm['labels']['experience']);
        $this->assertEquals('EDUCATION', $norm['labels']['education']);

        // Verify profile summary is the candidate's actual summary and NOT the AI critique
        $this->assertStringContainsString('DevOps engineer with 9 years of experience', $norm['profile_summary']);
        $this->assertStringNotContainsString('Ce CV est très bien structuré', $norm['profile_summary']);
        $this->assertStringNotContainsString('CORE SKILLS', $norm['profile_summary']);
        $this->assertStringNotContainsString('PROFESSIONAL EXPERIENCE', $norm['profile_summary']);
        $this->assertStringNotContainsString('EDUCATION', $norm['profile_summary']);

        // Verify certifications & languages extracted from raw text
        $this->assertNotEmpty($norm['certifications']);
        $this->assertNotEmpty($norm['languages']);

        // Test copy plain text uses English labels and candidate summary
        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'copy',
            'cv_id' => $analysis->id,
        ]);

        $response->assertStatus(200);
        $content = $response->json('cv_text');
        $this->assertStringContainsString('PROFESSIONAL SUMMARY', $content);
        $this->assertStringContainsString('DevOps engineer with 9 years of experience', $content);
        $this->assertStringNotContainsString('Ce CV est très bien structuré', $content);
        $this->assertStringContainsString('EDUCATION', $content);

        // Test human-readable file format label instead of raw technical MIME type
        $docxAnalysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_docx_test',
            'original_filename' => 'CV_Moussa_Diallo_DevOps_EN.docx',
            'file_path' => 'cv_analyses/moussa.docx',
            'file_size' => 10137,
            'mime_type' => self::MIME_DOCX,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'global_score' => 88,
            'status_label' => 'Excellent',
            'summary' => 'DevOps',
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $this->assertEquals('Document Word (.docx)', $docxAnalysis->file_format_label);
        $this->assertStringContainsString('text-blue-700', $docxAnalysis->file_format_badge_color);
        $this->assertEquals('Document PDF (.pdf)', $analysis->file_format_label);
    }

    public function test_cv_action_download_returns_signed_url_and_downloads_docx()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
            'credits_balance' => 10,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_docx_download',
            'original_filename' => 'CV_Moussa_Diallo.pdf',
            'file_path' => 'cv_analyses/users/'.$user->id.'/moussa.pdf',
            'file_size' => 15000,
            'mime_type' => self::MIME_PDF,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'candidate_title' => self::TEST_DEV_OPS_TITLE,
            'global_score' => 90,
            'status_label' => 'Excellent',
            'summary' => 'DevOps expert',
            'parsed_content' => [
                'experiences' => [
                    'Lead DevOps Engineer - Tech Corp (3 ans) : Deployed Kubernetes.',
                ],
                'skills' => ['Docker', 'Kubernetes'],
            ],
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'download',
            'cv_id' => $analysis->id,
            'template' => 0,
        ]);

        $response->assertStatus(200);
        $downloadUrl = $response->json('download_url');
        $this->assertNotEmpty($downloadUrl);

        $downloadResponse = $this->actingAs($user)->get($downloadUrl);
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString(self::MIME_DOCX, $downloadResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('CV_moussa_diallo_ATS.docx', $downloadResponse->headers->get('Content-Disposition'));

        // Test download_docx action explicitly
        $responseDocx = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'download_docx',
            'cv_id' => $analysis->id,
            'template' => 0,
        ]);
        $responseDocx->assertStatus(200);
        $this->assertNotEmpty($responseDocx->json('download_url'));
        $this->assertEquals('docx', $responseDocx->json('format'));

        // Test download_pdf action
        $responsePdf = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'action' => 'download_pdf',
            'cv_id' => $analysis->id,
            'template' => 0,
        ]);
        $responsePdf->assertStatus(200);
        $this->assertEquals('pdf', $responsePdf->json('format'));
        $this->assertTrue($responsePdf->json('success'));
    }

    public function test_authenticated_cv_upload_stores_document_in_academic_documents()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $file = UploadedFile::fake()->create('Mon_CV_Original.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post(route('jeune.cv.analyze'), [
            'cv_file' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('academic_documents', [
            'user_id' => $user->id,
            'document_type' => 'cv',
            'file_name' => 'Mon_CV_Original.pdf',
        ]);
    }

    public function test_claiming_guest_cv_stores_document_in_academic_documents()
    {
        $file = UploadedFile::fake()->create('CV_Guest_Original.docx', 250, self::MIME_DOCX);

        $this->post(route('public.opportunities.analyze'), [
            'cv_file' => $file,
        ]);

        $guestToken = session('pending_cv_token');
        $this->assertNotNull($guestToken);

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        // Simuler la visite de la page outils avec le token en session
        $this->actingAs($user)->withSession(['pending_cv_token' => $guestToken])
            ->get(route('jeune.outils'));

        $this->assertDatabaseHas('academic_documents', [
            'user_id' => $user->id,
            'document_type' => 'cv',
            'file_name' => 'CV_Guest_Original.docx',
        ]);
    }

    public function test_original_cv_download_button_is_conditional_and_serves_exact_file()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $analysis = CvAnalysis::create([
            'user_id' => $user->id,
            'guest_token' => 'token_bypass_check',
            'original_filename' => 'CV_Word_Original.docx',
            'file_path' => 'cv_analyses/test_original.docx',
            'file_size' => 10000,
            'mime_type' => self::MIME_DOCX,
            'candidate_name' => self::TEST_CANDIDATE_NAME,
            'global_score' => 85,
            'status_label' => 'Bien',
            'summary' => 'DevOps',
            'parsed_content' => [
                'raw_text' => 'MOUSSA DIALLO\nSenior DevOps Engineer',
            ],
            'criteria_scores' => [],
            'strengths' => [],
            'improvements' => [],
            'recommendations' => [],
            'is_claimed' => true,
        ]);

        // 1. Quand le fichier physique n'est PAS trouvé sur le disque, le bouton n'apparaît pas
        $responseWithoutFile = $this->actingAs($user)->get(route('jeune.outils', ['tab' => 'cv', 'cv_id' => $analysis->id]));
        $responseWithoutFile->assertStatus(200);
        $responseWithoutFile->assertDontSee('Télécharger le CV original');

        // 2. Quand le fichier physique est disponible sur le disque, le bouton apparaît
        Storage::disk('public')->put('cv_analyses/test_original.docx', 'Fichier Word Original Brut');
        $responseWithFile = $this->actingAs($user)->get(route('jeune.outils', ['tab' => 'cv', 'cv_id' => $analysis->id]));
        $responseWithFile->assertStatus(200);
        $responseWithFile->assertSee('Télécharger le CV original');

        // 3. Le clic sur le bouton télécharge exactement le fichier original uploadé
        $downloadResponse = $this->actingAs($user)->get(route('jeune.cv.download-original', $analysis->id));
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('CV_Word_Original.docx', $downloadResponse->headers->get('Content-Disposition'));
        $this->assertEquals('Fichier Word Original Brut', $downloadResponse->streamedContent());
    }

    public function test_rapid_multiple_cv_uploads_do_not_create_duplicates()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $file1 = UploadedFile::fake()->create('Mon_CV_Unique.pdf', 350, 'application/pdf');

        // Première soumission
        $this->actingAs($user)->post(route('jeune.cv.analyze'), ['cv_file' => $file1]);

        // Deuxième soumission en rafale immédiate du même fichier
        $file2 = UploadedFile::fake()->create('Mon_CV_Unique.pdf', 350, 'application/pdf');
        $this->actingAs($user)->post(route('jeune.cv.analyze'), ['cv_file' => $file2]);

        // Vérifier qu'une seule analyse a été persistée pour cet upload répété
        $this->assertEquals(1, $user->cvAnalyses()->where('original_filename', 'Mon_CV_Unique.pdf')->count());
        $this->assertEquals(1, $user->academicDocuments()->where('file_name', 'Mon_CV_Unique.pdf')->count());
    }

    public function test_view_document_serves_html_preview_for_docx()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        Storage::disk('public')->put('documents/test.docx', 'PK mock docx content');

        $doc = $user->academicDocuments()->create([
            'document_type' => 'cv',
            'file_name' => 'Mon_CV.docx',
            'file_path' => 'documents/test.docx',
            'file_size' => 12000,
            'mime_type' => self::MIME_DOCX,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('jeune.documents.view', $doc->id));
        $response->assertStatus(200);
        $response->assertSee('DOCX');
        $response->assertSee('Mon_CV.docx');
        $response->assertSee('Télécharger le fichier (.docx)');
    }

    public function test_reevaluer_button_is_removed_and_modal_has_no_ia_mention()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.outils', ['tab' => 'cv']));
        $response->assertStatus(200);
        $response->assertDontSee('Réévaluer un CV');
        $response->assertDontSee("Évaluer un CV avec l'IA");
        $response->assertDontSee("Lancer l'analyse IA");
        $response->assertSee('Évaluer un nouveau CV');
        $response->assertSee("Lancer l'analyse", false);
    }

    public function test_download_docx_cv_serves_valid_document_matching_chosen_template()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => true,
        ]);

        $analysis = $this->createCvAnalysisFixture($user, [
            'guest_token' => 'token_docx_template_check',
            'original_filename' => 'CV_Fatoumata_Traore.docx',
            'file_path' => 'cv_analyses/test_fatoumata.docx',
            'file_size' => 8000,
            'mime_type' => self::MIME_DOCX,
            'candidate_name' => 'Fatoumata Traoré',
            'candidate_title' => 'UX/UI Designer Junior',
            'global_score' => 88,
            'status_label' => 'Excellent',
            'summary' => 'Profil UX Designer',
            'parsed_content' => [
                'raw_text' => "FATOUMATA TRAORÉ\nUX/UI Designer Junior\nRÉSUMÉ PROFESSIONNEL\nAncienne assistante sociale reconvertie en UX/UI Designer avec expérience en recherche utilisateur.",
            ],
        ]);

        // L'utilisateur a mis le coût du template moderne à 0 dans le backoffice
        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_cv_template_4'],
            ['value' => '0', 'group' => 'features', 'type' => 'integer']
        );

        // Téléchargement Template 4 (Expert Moderne)
        $response = $this->actingAs($user)->get(route('jeune.cv.download-docx', [
            'cv' => $analysis->id,
            'template' => 4,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', self::MIME_DOCX);
        $this->assertStringContainsString('CV_fatoumata_traore_ATS.docx', $response->headers->get('Content-Disposition'));

        $xml = $this->extractDocxXmlContent($response, 'test_verify_t4_');

        // Vérification des éléments distinctifs de la maquette Expert Moderne
        $this->assertStringContainsString('ModernDarkBanner', $xml);
        $this->assertStringContainsString('111827', $xml); // Fond sombre du bandeau
        $this->assertStringContainsString('10B981', $xml); // Soulignement émeraude
        $this->assertStringContainsString('FATOUMATA TRAORÉ', $xml);
        $this->assertStringContainsString('UX/UI Designer Junior', $xml);
        $this->assertStringContainsString('SYNTHÈSE EXÉCUTIVE', $xml);
    }

    public function test_cv_restructured_content_applies_recommendations_with_placeholders_in_web_and_docx()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'email' => 'candidat.star@brillio.test',
        ]);

        $analysis = $this->createCvAnalysisFixture($user, [
            'guest_token' => 'token_star_restructured',
            'original_filename' => 'CV_Candidat_Ameliore.pdf',
            'file_path' => 'cv_analyses/test_star.pdf',
            'parsed_content' => [
                'profil' => 'Développeur passionné orienté résultats avec expertise en Laravel et Vue.js [À compléter : années d\'expérience ou impact majeur].',
                'experiences' => [
                    [
                        'title' => 'Développeur Web & Mobile (Stagiaire)',
                        'company' => 'Tech Solutions SARL',
                        'period' => 'Janv 2024 - Présent',
                        'bullets' => [
                            'Conception et développement d\'une API REST sous Laravel permettant de fluidifier les échanges de données [À compléter : +30% de rapidité / 500 requêtes/sec]',
                            'Optimisation des requêtes SQL et refonte du tableau de bord de suivi [À compléter : temps de réponse divisé par 2]',
                        ],
                    ],
                ],
                'formation' => [
                    [
                        'degree' => 'Master en Ingénierie Logicielle',
                        'school' => 'INP-HB Yamoussoukro',
                        'year' => '2023 - 2024',
                    ],
                ],
                'competences' => ['Laravel', 'Vue.js', 'PostgreSQL', 'Docker'],
                'langues' => ['Français (Courant)', 'Anglais (Professionnel)'],
            ],
        ]);

        // 1. Vérification de la normalisation des données
        $norm = $analysis->normalized_cv_data;
        $this->assertCount(1, $norm['experiences']);
        $this->assertEquals('Développeur Web & Mobile (Stagiaire)', $norm['experiences'][0]['title']);
        $this->assertCount(2, $norm['experiences'][0]['bullets']);
        $this->assertStringContainsString('[À compléter : +30% de rapidité', $norm['experiences'][0]['bullets'][0]);

        // 2. Vérification du rendu Web dans outils.blade.php
        $webResponse = $this->actingAs($user)->get(route('jeune.outils'));
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Contenu restructuré', false);
        $webResponse->assertSee('Recommandations ATS appliquées', false);
        $webResponse->assertSee('bg-amber-100 text-amber-900 border border-amber-300', false);
        $webResponse->assertSee('À compléter : +30% de rapidité', false);

        // 3. Vérification de l'export Word .docx avec balises stylisées en ambre
        $docxResponse = $this->actingAs($user)->get(route('jeune.cv.download-docx', [
            'cv' => $analysis->id,
            'template' => 0,
        ]));
        $docxResponse->assertStatus(200);

        $xml = $this->extractDocxXmlContent($docxResponse, 'test_star_docx_');

        $this->assertStringContainsString('B45309', $xml); // Couleur ambre des balises
        $this->assertStringContainsString('À compléter : +30% de rapidité', $xml);
    }

    public function test_cv_inline_placeholder_editing_saves_to_model_and_renders_cleanly_in_copy_and_docx(): void
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 10,
        ]);

        $analysis = $this->createCvAnalysisFixture($user, [
            'guest_token' => 'token_test_inline_edit',
            'original_filename' => 'Mon_CV.pdf',
            'file_path' => 'cv_analyses/test_inline.pdf',
            'candidate_name' => 'Kouame Yao',
            'candidate_title' => 'Développeur Full Stack',
            'global_score' => 80,
            'status_label' => 'Bien',
            'parsed_content' => [
                'summary' => 'Passionné par le web avec [À compléter : X années] d\'expérience.',
                'experiences' => [
                    [
                        'title' => 'Développeur Full Stack',
                        'company' => 'Tech SARL',
                        'dates' => '2022 - 2024',
                        'location' => 'Abidjan',
                        'bullets' => [
                            'Développé une API traitant [À compléter : nombre de] requêtes par jour.',
                        ],
                    ],
                ],
                'formations' => [],
                'competences' => ['PHP', 'Laravel'],
                'langues' => ['Français'],
            ],
        ]);

        // 1. Mise à jour inline du placeholder d'une expérience (bullet 0)
        $updateResponse = $this->actingAs($user)->postJson(route('jeune.cv.update-placeholder'), [
            'cv_id' => $analysis->id,
            'field_type' => 'experience',
            'exp_index' => 0,
            'bullet_index' => 0,
            'original_tag' => '[À compléter : nombre de]',
            'custom_value' => self::TEST_CUSTOM_VALUE_REQUESTS,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson([
            'success' => true,
            'is_filled' => true,
            'custom_value' => self::TEST_CUSTOM_VALUE_REQUESTS,
            'new_tag' => '[rempli:'.self::TEST_CUSTOM_VALUE_REQUESTS.'|guide:À compléter : nombre de]',
        ]);

        // Vérifier la persistance dans le modèle CvAnalysis
        $analysis->refresh();
        $updatedBullet = $analysis->parsed_content['experiences'][0]['bullets'][0];
        $this->assertEquals(
            'Développé une API traitant [rempli:'.self::TEST_CUSTOM_VALUE_REQUESTS.'|guide:À compléter : nombre de] requêtes par jour.',
            $updatedBullet
        );

        // 2. Mise à jour inline du résumé (summary)
        $summaryUpdateResponse = $this->actingAs($user)->postJson(route('jeune.cv.update-placeholder'), [
            'cv_id' => $analysis->id,
            'field_type' => 'summary',
            'original_tag' => '[À compléter : X années]',
            'custom_value' => self::TEST_CUSTOM_VALUE_YEARS,
        ]);

        $summaryUpdateResponse->assertStatus(200);
        $summaryUpdateResponse->assertJson([
            'success' => true,
            'is_filled' => true,
            'custom_value' => self::TEST_CUSTOM_VALUE_YEARS,
        ]);

        $analysis->refresh();
        $this->assertEquals(
            'Passionné par le web avec [rempli:'.self::TEST_CUSTOM_VALUE_YEARS.'|guide:À compléter : X années] d\'expérience.',
            $analysis->parsed_content['summary']
        );

        // 3. Vérification du rendu dans la page Outils (affiche les valeurs complétées)
        $pageResponse = $this->actingAs($user)->get(route('jeune.outils'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee(self::TEST_CUSTOM_VALUE_REQUESTS, false);
        $pageResponse->assertSee(self::TEST_CUSTOM_VALUE_YEARS, false);

        // 4. Vérification de l'action "Copier le texte" : les balises [rempli:valeur|guide:...] doivent être nettoyées
        $copyResponse = $this->actingAs($user)->postJson(route('jeune.cv.action'), [
            'cv_id' => $analysis->id,
            'action' => 'copy',
            'template' => 0,
        ]);

        $copyResponse->assertStatus(200);
        $plainText = $copyResponse->json('cv_text');
        $this->assertStringContainsString(self::TEST_CUSTOM_VALUE_REQUESTS.' requêtes par jour', $plainText);
        $this->assertStringContainsString(self::TEST_CUSTOM_VALUE_YEARS.' d\'expérience', $plainText);
        $this->assertStringNotContainsString('rempli:', $plainText);
        $this->assertStringNotContainsString('guide:', $plainText);

        // 5. Vérification de l'export Word (.docx) : la valeur personnalisée est présente sans balises résiduelles
        $docxResponse = $this->actingAs($user)->get(route('jeune.cv.download-docx', [
            'cv' => $analysis->id,
            'template' => 0,
        ]));
        $docxResponse->assertStatus(200);

        $xml = $this->extractDocxXmlContent($docxResponse, 'test_filled_docx_');

        $this->assertStringContainsString(self::TEST_CUSTOM_VALUE_REQUESTS, $xml);
        $this->assertStringContainsString(self::TEST_CUSTOM_VALUE_YEARS, $xml);
        $this->assertStringNotContainsString('rempli:', $xml);
        $this->assertStringNotContainsString('guide:', $xml);

        // 6. Test de réinitialisation (effacer la valeur pour revenir à la balise guide initiale)
        $resetResponse = $this->actingAs($user)->postJson(route('jeune.cv.update-placeholder'), [
            'cv_id' => $analysis->id,
            'field_type' => 'experience',
            'exp_index' => 0,
            'bullet_index' => 0,
            'original_tag' => '[rempli:'.self::TEST_CUSTOM_VALUE_REQUESTS.'|guide:À compléter : nombre de]',
            'custom_value' => '', // valeur vide => rétablissement
        ]);

        $resetResponse->assertStatus(200);
        $resetResponse->assertJson([
            'success' => true,
            'is_filled' => false,
            'new_tag' => '[À compléter : nombre de]',
        ]);

        $analysis->refresh();
        $this->assertEquals(
            'Développé une API traitant [À compléter : nombre de] requêtes par jour.',
            $analysis->parsed_content['experiences'][0]['bullets'][0]
        );
    }

    public function test_cv_print_media_rules_enforce_single_page_and_isolate_print_area(): void
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
        ]);

        $response = $this->actingAs($user)->get(route('jeune.outils'));
        $response->assertStatus(200);

        // Vérifie les règles CSS clés pour l'impression A4 sur 1 page
        $response->assertSee('@media print', false);
        $response->assertSee('size: A4 portrait;', false);
        $response->assertSee('#cvEnhancedPrintArea', false);
        $response->assertSee('position: static !important;', false);
        $response->assertSee('page-break-after: avoid !important;', false);
        $response->assertSee('.no-print', false);
    }
}

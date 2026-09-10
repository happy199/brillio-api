<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicDocument;
use App\Models\CvAnalysis;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCvAnalyticsAndAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $jeune;

    protected CvAnalysis $cvAnalysis;

    protected AcademicDocument $cvDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->jeune = User::factory()->create([
            'user_type' => 'jeune',
            'name' => 'Aya N\'Guessan',
            'email' => 'aya.nguessan@example.com',
            'phone' => '+225 07 00 00 00 00',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        $this->cvAnalysis = CvAnalysis::create([
            'user_id' => $this->jeune->id,
            'guest_token' => 'token_admin_test_123',
            'original_filename' => 'CV_Aya_Nguessan.pdf',
            'file_path' => 'cv_analyses/test_aya_admin.pdf',
            'file_size' => 10240,
            'mime_type' => 'application/pdf',
            'candidate_name' => 'Aya N\'Guessan',
            'candidate_title' => 'Développeuse Frontend Junior',
            'candidate_contact' => [
                'email' => 'aya.nguessan@example.com',
                'phone' => '+225 07 00 00 00 00',
                'location' => 'Abidjan, Côte d\'Ivoire',
            ],
            'global_score' => 84,
            'criteria_scores' => [
                'structure' => 85,
                'clarity' => 80,
                'chronology' => 90,
                'quantification' => 82,
                'keywords' => 85,
            ],
            'strengths' => [
                'Expérience concrète avec React et JavaScript.',
                'Projets réels déployés avec impact mesurable.',
            ],
            'weaknesses' => [
                'Chiffrer davantage les missions administratives.',
            ],
            'recommendations' => [
                'Appliquer la méthode STAR sur chaque réalisation.',
            ],
            'parsed_content' => [
                'profil' => 'Développeuse frontend passionnée et orientée résultats.',
                'experiences' => [
                    [
                        'title' => 'Développeuse Frontend (Projet Personnel)',
                        'company' => 'Système de réservation',
                        'period' => '2025',
                        'bullets' => ['Création d\'une interface interactive en React.'],
                    ],
                ],
                'competences' => ['React', 'JavaScript', 'CSS3', 'HTML5', 'Git'],
                'formation' => [
                    [
                        'degree' => 'Développement Web',
                        'school' => 'École de code',
                        'year' => '2025',
                    ],
                ],
            ],
            'summary' => 'Profil prometteur avec de très bonnes réalisations techniques.',
            'is_claimed' => true,
        ]);

        $this->cvDocument = $this->jeune->academicDocuments()->create([
            'document_type' => 'cv',
            'file_name' => 'CV_Aya_Nguessan.pdf',
            'file_path' => 'cv_analyses/test_aya_admin.pdf',
            'file_size' => 10240,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);
    }

    public function test_admin_dashboard_displays_cv_analysis_card()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Analyses de CV', false);
        $response->assertSee('Score moy.', false);
        $response->assertSee('84/100', false);
    }

    public function test_admin_documents_page_displays_cv_and_has_ai_propositions_and_export_buttons()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.documents.index'));
        $response->assertStatus(200);
        $response->assertSee('CVs Originaux', false);
        $response->assertSee('Propositions IA', false);
        $response->assertSee('Extraction Commerciale (CV)', false);
    }

    public function test_admin_can_retrieve_cv_ai_analysis_data()
    {
        $response = $this->actingAs($this->admin)->getJson(route('admin.documents.cv-analysis', $this->cvDocument));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'candidate_name' => 'Aya N\'Guessan',
                'candidate_title' => 'Développeuse Frontend Junior',
                'global_score' => 84,
            ],
        ]);
        $response->assertJsonPath('analysis.contact.phone', '+225 07 00 00 00 00');
    }

    public function test_admin_can_export_candidate_data_with_selected_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.documents.export-candidates'), [
            'fields' => ['candidate_name', 'email', 'phone', 'location', 'candidate_title', 'global_score'],
            'min_score' => 70,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nom et Prénom', $content);
        $this->assertStringContainsString('Aya N\'Guessan', $content);
        $this->assertStringContainsString('aya.nguessan@example.com', $content);
        $this->assertStringContainsString('+225 07 00 00 00 00', $content);
        $this->assertStringContainsString('Développeuse Frontend Junior', $content);
        $this->assertStringContainsString('84', $content);
    }

    public function test_accounting_tracks_cv_action_as_company_revenue()
    {
        // Création d'une transaction de débit pour l'achat d'un template CV
        WalletTransaction::create([
            'user_id' => $this->jeune->id,
            'amount' => -5,
            'type' => 'cv_action',
            'description' => 'Téléchargement CV ATS Word (Template Avancé Cadre)',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.accounting.index'));
        $response->assertStatus(200);
        $response->assertViewHas('cvRevenueCredits', 5);
        $response->assertViewHas('estimatedCvRevenueFcfa', 500);
        $response->assertSee('Gains CV & Templates', false);

        // Vérification dans l'export Excel comptable
        $excelResponse = $this->actingAs($this->admin)->get(route('admin.accounting.export-excel'));
        $excelContent = $excelResponse->streamedContent();
        $this->assertStringContainsString('Gains Outils CV & Templates (Crédits)', $excelContent);
        $this->assertStringContainsString('5 Crédits', $excelContent);
    }

    public function test_legal_pages_include_cv_ai_and_placement_provisions()
    {
        // Test CGU
        $termsResponse = $this->get(route('terms'));
        $termsResponse->assertStatus(200);
        $termsResponse->assertSee('Outils d\'Analyse de CV, Templates et Crédits', false);
        $termsResponse->assertSee('équipe d\'accompagnement et de placement de Brillio', false);

        // Test Politique de confidentialité
        $privacyPolicyResponse = $this->get(route('privacy-policy'));
        $privacyPolicyResponse->assertStatus(200);
        $privacyPolicyResponse->assertSee('Curriculum Vitae (CV)', false);
        $privacyPolicyResponse->assertSee('critères ATS', false);
    }
}

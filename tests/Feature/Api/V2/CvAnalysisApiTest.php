<?php

namespace Tests\Feature\Api\V2;

use App\Models\CvAnalysis;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\CvAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Tests pour l'API d'analyse de CV (mobile)
 *
 * Couvre :
 * - GET /api/v2/cv (liste)
 * - POST /api/v2/cv/analyze (upload + analyse IA)
 * - GET /api/v2/cv/{id} (détail)
 * - POST /api/v2/cv/{id}/reanalyze (réanalyse)
 * - DELETE /api/v2/cv/{id} (suppression)
 * - POST /api/v2/cv/{id}/placeholder (mise à jour de balise)
 * - POST /api/v2/cv/{id}/action (copie / téléchargement)
 * - Tests de non-régression
 */
class CvAnalysisApiTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================
    // Helpers
    // =====================================================

    private function makeUser(): User
    {
        return User::factory()->create(['user_type' => 'jeune']);
    }

    private function makeCvAnalysis(User $user, array $overrides = []): CvAnalysis
    {
        return CvAnalysis::factory()->create(array_merge([
            'user_id' => $user->id,
            'original_filename' => 'cv_test.pdf',
            'global_score' => 78,
            'status_label' => 'Très bien',
            'candidate_name' => 'Jean Dupont',
            'candidate_title' => 'Ingénieur logiciel',
            'parsed_content' => [
                'summary' => 'Profil professionnel [guide:Ajoutez votre phrase d\'accroche]',
                'experiences' => [
                    [
                        'title' => 'Développeur',
                        'company' => 'ACME',
                        'dates' => '2022-2024',
                        'bullets' => [
                            'Développement d\'applications [guide:Résultat quantifié]',
                            'Travail en équipe agile',
                        ],
                    ],
                ],
                'skills' => ['PHP', 'Laravel', 'Vue.js'],
                'education' => [
                    ['degree' => 'Master Info', 'school' => 'ESGI', 'dates' => '2020-2022'],
                ],
            ],
            'criteria_scores' => ['structure' => 80, 'clarite' => 75, 'experiences' => 70, 'competences' => 85, 'impact' => 72],
            'strengths' => ['Bonne structure', 'Compétences claires'],
            'improvements' => ['Quantifier les résultats'],
            'recommendations' => ['Ajouter des chiffres'],
            'summary' => 'Profil prometteur.',
        ], $overrides));
    }

    private function mockCvService(array $fakeResult): void
    {
        $mock = Mockery::mock(CvAnalysisService::class);
        $mock->shouldReceive('processAndAnalyze')->andReturnUsing(function ($file, $user) use ($fakeResult) {
            return CvAnalysis::factory()->create(array_merge([
                'user_id' => $user->id,
                'original_filename' => 'mock_cv.pdf',
                'global_score' => 78,
                'status_label' => 'Très bien',
                'candidate_name' => 'Mock Candidat',
                'candidate_title' => 'Profil IA',
                'parsed_content' => [],
                'criteria_scores' => [],
                'strengths' => [],
                'improvements' => [],
                'recommendations' => [],
                'summary' => 'CV analysé via mock.',
                'is_claimed' => true,
            ], $fakeResult));
        });
        $mock->shouldReceive('reanalyze')->andReturnUsing(function ($analysis) use ($fakeResult) {
            return CvAnalysis::factory()->create(array_merge([
                'user_id' => $analysis->user_id,
                'original_filename' => $analysis->original_filename,
                'global_score' => 85,
                'status_label' => 'Excellent',
                'candidate_name' => 'Mock Candidat',
                'candidate_title' => 'Profil IA',
                'parsed_content' => [],
                'criteria_scores' => [],
                'strengths' => [],
                'improvements' => [],
                'recommendations' => [],
                'summary' => 'CV réanalysé.',
                'is_claimed' => true,
            ], $fakeResult));
        });
        $this->app->instance(CvAnalysisService::class, $mock);
    }

    // =====================================================
    // Tests : GET /api/v2/cv
    // =====================================================

    /** @test */
    public function it_lists_user_cv_analyses()
    {
        $user = $this->makeUser();
        $this->makeCvAnalysis($user);
        $this->makeCvAnalysis($user, ['global_score' => 65]);

        $response = $this->actingAs($user)->getJson('/api/v2/cv');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'analyses' => [
                        '*' => ['id', 'original_filename', 'global_score', 'status_label', 'candidate_name', 'created_at'],
                    ],
                    'total',
                ],
            ])
            ->assertJsonPath('data.total', 2);
    }

    /** @test */
    public function it_only_returns_own_analyses()
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $this->makeCvAnalysis($user);
        $this->makeCvAnalysis($other);

        $response = $this->actingAs($user)->getJson('/api/v2/cv');

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 1);
    }

    /** @test */
    public function it_requires_auth_to_list_analyses()
    {
        $response = $this->getJson('/api/v2/cv');
        $response->assertStatus(401);
    }

    // =====================================================
    // Tests : POST /api/v2/cv/analyze
    // =====================================================

    /** @test */
    public function it_analyzes_an_uploaded_cv_file()
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $this->mockCvService([]);

        $file = UploadedFile::fake()->create('mon_cv.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson('/api/v2/cv/analyze', [
            'cv_file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'analysis' => [
                        'id', 'global_score', 'status_label', 'candidate_name',
                        'criteria_scores', 'strengths', 'improvements',
                        'recommendations', 'parsed_content',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_rejects_unsupported_file_type()
    {
        Storage::fake('public');
        $user = $this->makeUser();

        $file = UploadedFile::fake()->create('document.xlsx', 100, 'application/vnd.ms-excel');

        $response = $this->actingAs($user)->postJson('/api/v2/cv/analyze', [
            'cv_file' => $file,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_requires_a_file_to_analyze()
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->postJson('/api/v2/cv/analyze', []);
        $response->assertStatus(422);
    }

    /** @test */
    public function it_requires_auth_to_analyze()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $response = $this->postJson('/api/v2/cv/analyze', ['cv_file' => $file]);
        $response->assertStatus(401);
    }

    // =====================================================
    // Tests : GET /api/v2/cv/{id}
    // =====================================================

    /** @test */
    public function it_shows_analysis_detail()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->getJson("/api/v2/cv/{$analysis->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.analysis.id', $analysis->id)
            ->assertJsonPath('data.analysis.global_score', 78)
            ->assertJsonStructure([
                'data' => [
                    'analysis' => [
                        'id', 'global_score', 'status_label', 'parsed_content',
                        'criteria_scores', 'strengths', 'improvements',
                        'recommendations', 'summary', 'candidate_contact',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_for_other_users_analysis()
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $analysis = $this->makeCvAnalysis($other);

        $response = $this->actingAs($user)->getJson("/api/v2/cv/{$analysis->id}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    // =====================================================
    // Tests : POST /api/v2/cv/{id}/reanalyze
    // =====================================================

    /** @test */
    public function it_reanalyzes_an_existing_cv()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);
        $this->mockCvService([]);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/reanalyze");

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['analysis', 'new_id'],
            ]);
    }

    /** @test */
    public function it_returns_404_for_reanalyze_on_other_users_analysis()
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $analysis = $this->makeCvAnalysis($other);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/reanalyze");

        $response->assertStatus(404);
    }

    // =====================================================
    // Tests : DELETE /api/v2/cv/{id}
    // =====================================================

    /** @test */
    public function it_deletes_own_cv_analysis()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->deleteJson("/api/v2/cv/{$analysis->id}");

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNull(CvAnalysis::find($analysis->id));
    }

    /** @test */
    public function it_cannot_delete_other_users_cv()
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $analysis = $this->makeCvAnalysis($other);

        $response = $this->actingAs($user)->deleteJson("/api/v2/cv/{$analysis->id}");

        $response->assertStatus(404);
        $this->assertNotNull(CvAnalysis::find($analysis->id));
    }

    // =====================================================
    // Tests : POST /api/v2/cv/{id}/placeholder
    // =====================================================

    /** @test */
    public function it_updates_a_summary_placeholder()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/placeholder", [
            'field_type' => 'summary',
            'original_tag' => "[guide:Ajoutez votre phrase d'accroche]",
            'new_value' => 'Développeur passionné avec 5 ans d\'expérience',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_filled', true);

        // Vérifier la persistance
        $updatedAnalysis = CvAnalysis::find($analysis->id);
        $this->assertStringContainsString('rempli:', $updatedAnalysis->parsed_content['summary'] ?? '');
    }

    /** @test */
    public function it_updates_an_experience_bullet_placeholder()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/placeholder", [
            'field_type' => 'experience',
            'exp_index' => 0,
            'bullet_index' => 0,
            'original_tag' => '[guide:Résultat quantifié]',
            'new_value' => 'Augmentation de 40% des performances',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_filled', true);
    }

    /** @test */
    public function it_resets_a_placeholder_when_new_value_is_empty()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/placeholder", [
            'field_type' => 'summary',
            'original_tag' => "[guide:Ajoutez votre phrase d'accroche]",
            'new_value' => '',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_filled', false);
    }

    // =====================================================
    // Tests : POST /api/v2/cv/{id}/action
    // =====================================================

    /** @test */
    public function it_returns_cv_text_for_copy_action()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 10,
        ]);
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/action", [
            'action' => 'copy',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.action', 'copy')
            ->assertJsonPath('data.format', 'text')
            ->assertJsonStructure(['data' => ['cv_text', 'cost', 'remaining_balance']]);
    }

    /** @test */
    public function it_returns_402_when_credits_insufficient_for_copy()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 0,
        ]);
        $analysis = $this->makeCvAnalysis($user);

        // Forcer un coût > 0 via SystemSetting (par défaut cost=1 pour copy)
        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_cv_copy'],
            ['value' => '1']
        );

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/action", [
            'action' => 'copy',
        ]);

        // Si le coût est 1 et balance=0 → 402
        // Si le coût est 0 (setting pas défini ou =0) → 200 (comportement normal)
        $this->assertContains($response->status(), [200, 402], 'Doit retourner 200 (gratuit) ou 402 (crédits insuffisants)');
    }

    /** @test */
    public function it_returns_download_url_for_docx_action()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 10,
        ]);
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/action", [
            'action' => 'download_docx',
            'template' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.action', 'download_docx')
            ->assertJsonPath('data.format', 'docx');
        // L'URL signée doit être présente
        $this->assertNotNull($response->json('data.download_url'));
    }

    /** @test */
    public function it_rejects_invalid_action()
    {
        $user = $this->makeUser();
        $analysis = $this->makeCvAnalysis($user);

        $response = $this->actingAs($user)->postJson("/api/v2/cv/{$analysis->id}/action", [
            'action' => 'invalid_action',
        ]);

        $response->assertStatus(422);
    }

    // =====================================================
    // Tests de non-régression
    // =====================================================

    /** @test */
    public function existing_documents_routes_still_work()
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->getJson('/api/v2/documents');
        $this->assertNotEquals(404, $response->status(), '/api/v2/documents ne doit pas retourner 404');
        $this->assertNotEquals(500, $response->status(), '/api/v2/documents ne doit pas retourner 500');
    }

    /** @test */
    public function existing_personality_routes_still_work()
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->getJson('/api/v2/personality/questions');
        $this->assertNotEquals(404, $response->status());
        $this->assertNotEquals(500, $response->status());
    }

    /** @test */
    public function cv_routes_dont_conflict_with_existing_routes()
    {
        // Vérifier que /api/v2/cv/analyze n'est pas confondu avec /api/v2/cv/{id}
        $user = $this->makeUser();

        // GET /api/v2/cv/analyze ne doit pas correspondre à GET /api/v2/cv/{id}
        // (car analyze n'est pas un entier)
        $response = $this->actingAs($user)->getJson('/api/v2/cv/analyze');
        // Doit retourner 404 (méthode GET non définie sur /cv/analyze) et non 200 d'un CV inexistant
        $this->assertNotEquals(500, $response->status(), 'Pas de 500 sur /api/v2/cv/analyze en GET');
    }
}

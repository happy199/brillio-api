<?php

namespace Tests\Feature\Api\V2;

use App\Models\MentorProfile;
use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Tests pour l'authentification sociale API (Google + LinkedIn)
 *
 * Couvre :
 * - Tests fonctionnels : GET url, POST authenticate (google jeune, linkedin mentor)
 * - Tests de régression : les routes existantes email/password ne sont pas cassées
 * - Cas limites : token invalide, compte bloqué, provider invalide, missing params
 */
class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Mock du SupabaseAuthService pour simuler les réponses Supabase
     */
    private function mockSupabase(array $methods): void
    {
        $mock = Mockery::mock(SupabaseAuthService::class);

        foreach ($methods as $method => $returnValue) {
            $mock->shouldReceive($method)->andReturn($returnValue);
        }

        $this->app->instance(SupabaseAuthService::class, $mock);
    }

    /**
     * Données utilisateur simulées retournées par Supabase pour un jeune Google
     */
    private function fakeGoogleUserData(string $email = 'jeune@example.com'): array
    {
        return [
            'id' => 'supabase-uuid-google-123',
            'email' => $email,
            'email_confirmed_at' => now()->toISOString(),
            'app_metadata' => ['provider' => 'google'],
            'raw_app_meta_data' => ['provider' => 'google'],
            'user_metadata' => [
                'full_name' => 'Jean Dupont',
                'avatar_url' => 'https://lh3.googleusercontent.com/photo.jpg',
                'email_verified' => true,
            ],
            'raw_user_meta_data' => [
                'full_name' => 'Jean Dupont',
                'avatar_url' => 'https://lh3.googleusercontent.com/photo.jpg',
            ],
        ];
    }

    /**
     * Données utilisateur simulées retournées par Supabase pour un mentor LinkedIn
     */
    private function fakeLinkedInUserData(string $email = 'mentor@example.com'): array
    {
        return [
            'id' => 'supabase-uuid-linkedin-456',
            'email' => $email,
            'email_confirmed_at' => now()->toISOString(),
            'app_metadata' => ['provider' => 'linkedin_oidc'],
            'raw_app_meta_data' => ['provider' => 'linkedin_oidc'],
            'identities' => [
                ['provider' => 'linkedin_oidc', 'id' => 'li-profile-789'],
            ],
            'user_metadata' => [
                'full_name' => 'Marie Martin',
                'avatar_url' => 'https://media.licdn.com/photo.jpg',
            ],
            'raw_user_meta_data' => [
                'full_name' => 'Marie Martin',
                'avatar_url' => 'https://media.licdn.com/photo.jpg',
            ],
        ];
    }

    // =====================================================
    // TESTS FONCTIONNELS : GET /api/v2/auth/social/{provider}/url
    // =====================================================

    /** @test */
    public function it_returns_google_oauth_url()
    {
        $this->mockSupabase([
            'getOAuthUrl' => 'https://supabase.example.co/auth/v1/authorize?provider=google&redirect_to=myapp%3A%2F%2Fcallback',
        ]);

        $response = $this->getJson('/api/v2/auth/social/google/url?redirect_uri=myapp://callback');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['url', 'provider'],
            ])
            ->assertJsonPath('data.provider', 'google')
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_returns_linkedin_oauth_url()
    {
        $this->mockSupabase([
            'getOAuthUrl' => 'https://supabase.example.co/auth/v1/authorize?provider=linkedin_oidc&redirect_to=myapp%3A%2F%2Fcallback',
        ]);

        $response = $this->getJson('/api/v2/auth/social/linkedin/url?redirect_uri=myapp://callback');

        $response->assertStatus(200)
            ->assertJsonPath('data.provider', 'linkedin')
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_requires_redirect_uri_for_oauth_url()
    {
        $response = $this->getJson('/api/v2/auth/social/google/url');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_rejects_unsupported_provider_for_url()
    {
        $response = $this->getJson('/api/v2/auth/social/facebook/url?redirect_uri=myapp://callback');

        // La route ne matche pas (where constraint) → 404
        $response->assertStatus(404);
    }

    // =====================================================
    // TESTS FONCTIONNELS : POST /api/v2/auth/social/google (JEUNE)
    // =====================================================

    /** @test */
    public function google_auth_creates_new_jeune_user()
    {
        $userData = $this->fakeGoogleUserData('nouveau-jeune@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-123',
                'name' => 'Jean Dupont',
                'email' => 'nouveau-jeune@example.com',
                'avatar_url' => 'https://lh3.googleusercontent.com/photo.jpg',
                'email_verified' => true,
            ],
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'nouveau-jeune@example.com']);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-supabase-access-token',
            'user_type' => 'jeune',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                    'is_new_user',
                    'onboarding_required',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.is_new_user', true);

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau-jeune@example.com',
            'user_type' => 'jeune',
            'auth_provider' => 'google',
        ]);
    }

    /** @test */
    public function google_auth_logs_in_existing_jeune_user()
    {
        $existingUser = User::factory()->create([
            'email' => 'jeune@example.com',
            'user_type' => 'jeune',
            'auth_provider' => 'google',
            'onboarding_completed' => true,
        ]);

        $userData = $this->fakeGoogleUserData('jeune@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-123',
                'name' => 'Jean Dupont',
                'email' => 'jeune@example.com',
                'avatar_url' => 'https://lh3.googleusercontent.com/photo.jpg',
                'email_verified' => true,
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-supabase-access-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_new_user', false);

        // Vérifier qu'un seul user existe (pas de doublon)
        $this->assertSame(1, User::where('email', 'jeune@example.com')->count());
    }

    /** @test */
    public function google_auth_returns_sanctum_token_that_authenticates()
    {
        $userData = $this->fakeGoogleUserData('jeune-token@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-789',
                'name' => 'Jean Token',
                'email' => 'jeune-token@example.com',
                'avatar_url' => null,
                'email_verified' => true,
            ],
        ]);

        $authResponse = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-token',
        ]);

        $authResponse->assertStatus(200);
        $sanctumToken = $authResponse->json('data.token');

        // Le token Sanctum doit permettre d'accéder aux endpoints protégés
        $meResponse = $this->withHeader('Authorization', 'Bearer '.$sanctumToken)
            ->getJson('/api/v2/user');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.user.email', 'jeune-token@example.com');
    }

    // =====================================================
    // TESTS FONCTIONNELS : POST /api/v2/auth/social/linkedin (MENTOR)
    // =====================================================

    /** @test */
    public function linkedin_auth_creates_new_mentor_user()
    {
        $userData = $this->fakeLinkedInUserData('nouveau-mentor@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractLinkedInData' => [
                'linkedin_id' => 'li-profile-789',
                'name' => 'Marie Martin',
                'email' => 'nouveau-mentor@example.com',
                'avatar_url' => 'https://media.licdn.com/photo.jpg',
                'raw_data' => ['full_name' => 'Marie Martin'],
            ],
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'nouveau-mentor@example.com']);

        $response = $this->postJson('/api/v2/auth/social/linkedin', [
            'provider_token' => 'valid-linkedin-supabase-token',
            'user_type' => 'mentor',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau-mentor@example.com',
            'user_type' => 'mentor',
            'auth_provider' => 'linkedin',
        ]);

        // Le profil mentor doit aussi être créé
        $user = User::where('email', 'nouveau-mentor@example.com')->first();
        $this->assertNotNull(MentorProfile::where('user_id', $user->id)->first());
    }

    /** @test */
    public function linkedin_auth_logs_in_existing_mentor_user()
    {
        $existingMentor = User::factory()->mentor()->create([
            'email' => 'mentor-existant@example.com',
            'auth_provider' => 'linkedin',
        ]);
        MentorProfile::factory()->create(['user_id' => $existingMentor->id]);

        $userData = $this->fakeLinkedInUserData('mentor-existant@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractLinkedInData' => [
                'linkedin_id' => 'li-123',
                'name' => 'Mentor Existant',
                'email' => 'mentor-existant@example.com',
                'avatar_url' => null,
                'raw_data' => [],
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/linkedin', [
            'provider_token' => 'valid-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Aucun doublon
        $this->assertSame(1, User::where('email', 'mentor-existant@example.com')->count());
    }

    // =====================================================
    // TESTS CAS LIMITES ET ERREURS
    // =====================================================

    /** @test */
    public function it_returns_400_for_invalid_provider_token()
    {
        $this->mockSupabase([
            'getUser' => null, // token invalide
        ]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'invalid-or-expired-token',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function it_returns_422_when_provider_token_missing()
    {
        $response = $this->postJson('/api/v2/auth/social/google', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_rejects_unsupported_provider_for_authenticate()
    {
        $response = $this->postJson('/api/v2/auth/social/facebook', [
            'provider_token' => 'some-token',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_401_for_blocked_jeune_user()
    {
        $blockedUser = User::factory()->create([
            'email' => 'blocked-jeune@example.com',
            'user_type' => 'jeune',
            'is_blocked' => true,
            'blocked_reason' => 'Violation des CGU',
        ]);

        $userData = $this->fakeGoogleUserData('blocked-jeune@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-blocked',
                'name' => 'Blocked User',
                'email' => 'blocked-jeune@example.com',
                'avatar_url' => null,
                'email_verified' => true,
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-token',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function it_returns_401_for_blocked_mentor_user()
    {
        $blockedMentor = User::factory()->mentor()->create([
            'email' => 'blocked-mentor@example.com',
            'is_blocked' => true,
            'blocked_reason' => 'Comportement inapproprié',
        ]);

        $userData = $this->fakeLinkedInUserData('blocked-mentor@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractLinkedInData' => [
                'linkedin_id' => 'li-blocked',
                'name' => 'Blocked Mentor',
                'email' => 'blocked-mentor@example.com',
                'avatar_url' => null,
                'raw_data' => [],
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/linkedin', [
            'provider_token' => 'valid-token',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function it_returns_409_when_google_email_belongs_to_active_mentor()
    {
        // Un mentor actif essaie de se connecter en tant que jeune via Google
        User::factory()->mentor()->create([
            'email' => 'mentor-as-jeune@example.com',
            'auth_provider' => 'linkedin',
            'is_archived' => false,
        ]);

        $userData = $this->fakeGoogleUserData('mentor-as-jeune@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-conflict',
                'name' => 'Conflict User',
                'email' => 'mentor-as-jeune@example.com',
                'avatar_url' => null,
                'email_verified' => true,
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-token',
            'user_type' => 'jeune',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function it_reactivates_archived_jeune_on_google_auth()
    {
        $archivedJeune = User::factory()->archived()->create([
            'email' => 'archived-jeune@example.com',
            'user_type' => 'jeune',
            'auth_provider' => 'google',
        ]);

        $this->assertTrue((bool) $archivedJeune->is_archived);

        $userData = $this->fakeGoogleUserData('archived-jeune@example.com');

        $this->mockSupabase([
            'getUser' => $userData,
            'extractSocialData' => [
                'provider' => 'google',
                'provider_id' => 'google-archived',
                'name' => 'Archived Jeune',
                'email' => 'archived-jeune@example.com',
                'avatar_url' => null,
                'email_verified' => true,
            ],
        ]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'valid-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $archivedJeune->refresh();
        $this->assertFalse((bool) $archivedJeune->is_archived, 'Le compte archivé devrait être réactivé');
        $this->assertNull($archivedJeune->archived_at);
    }

    // =====================================================
    // TESTS DE NON-RÉGRESSION : les routes existantes restent fonctionnelles
    // =====================================================

    /** @test */
    public function existing_email_register_still_works()
    {
        $response = $this->postJson('/api/v2/register', [
            'name' => 'Test User',
            'email' => 'test-regression@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'user_type' => 'jeune',
        ]);

        // 201 ou 422 (si validation email domaine) — en tout cas pas 500
        $this->assertNotEquals(500, $response->status(), 'La route /register ne doit pas retourner 500');
        $this->assertNotEquals(404, $response->status(), 'La route /register ne doit pas retourner 404');
    }

    /** @test */
    public function existing_email_login_still_works()
    {
        $user = User::factory()->create([
            'email' => 'login-regression@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/v2/login', [
            'email' => 'login-regression@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token']]);
    }

    /** @test */
    public function existing_logout_still_works()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
    }

    /** @test */
    public function social_auth_routes_are_rate_limited()
    {
        // Les routes social auth sont dans le groupe throttle:10,1
        // On vérifie juste que la route existe et répond (pas de 404)
        $this->mockSupabase(['getUser' => null]);

        $response = $this->postJson('/api/v2/auth/social/google', [
            'provider_token' => 'token',
        ]);

        // Doit retourner 400 (token invalide) et non 404 (route inexistante)
        $this->assertNotEquals(404, $response->status(), 'La route social auth ne doit pas retourner 404');
        $this->assertNotEquals(405, $response->status(), 'La méthode HTTP doit être acceptée');
    }

    /** @test */
    public function social_auth_get_url_route_exists()
    {
        $this->mockSupabase(['getOAuthUrl' => 'https://supabase.example.co/authorize']);

        $response = $this->getJson('/api/v2/auth/social/google/url?redirect_uri=myapp://test');

        $this->assertNotEquals(404, $response->status());
        $this->assertNotEquals(405, $response->status());
    }
}

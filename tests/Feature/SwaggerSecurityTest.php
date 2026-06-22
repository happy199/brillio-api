<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SwaggerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_ROUTE = '/_test_swagger_route';

    protected function setUp(): void
    {
        parent::setUp();

        // Enregistrer une route temporaire protégée par le middleware swagger_secure pour faciliter le test
        Route::middleware(['web', 'swagger_secure'])->get(self::TEST_ROUTE, function () {
            return response()->json(['allowed' => true]);
        });
    }

    /**
     * Test que l'accès est autorisé en environnement local.
     */
    public function test_access_is_allowed_in_local_environment()
    {
        $this->artisan('config:clear');
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->getJson(self::TEST_ROUTE);

        $response->assertStatus(200);
        $response->assertJson(['allowed' => true]);
    }

    /**
     * Test que l'accès est refusé aux visiteurs en production.
     */
    public function test_access_is_blocked_for_guests_in_production()
    {
        $this->app->detectEnvironment(fn () => 'production');

        // Requête JSON -> Doit retourner une erreur 403
        $response = $this->getJson(self::TEST_ROUTE);
        $response->assertStatus(403);
        $response->assertJson(['success' => false]);

        // Requête standard -> Doit rediriger vers la page de login admin
        $response = $this->get(self::TEST_ROUTE);
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Test que l'accès est refusé aux utilisateurs non-admin (ex: Jeune) en production.
     */
    public function test_access_is_blocked_for_non_admins_in_production()
    {
        $this->app->detectEnvironment(fn () => 'production');

        $jeune = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($jeune)
            ->getJson(self::TEST_ROUTE);

        $response->assertStatus(403);
    }

    /**
     * Test que l'accès est autorisé pour les administrateurs en production.
     */
    public function test_access_is_allowed_for_admins_in_production()
    {
        $this->app->detectEnvironment(fn () => 'production');

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(self::TEST_ROUTE);

        $response->assertStatus(200);
        $response->assertJson(['allowed' => true]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function manifest_json_is_accessible_and_valid()
    {
        $response = $this->get('/manifest.json');

        $response->assertStatus(200);
        $manifest = json_decode($response->getContent(), true);

        $this->assertIsArray($manifest);
        $this->assertEquals('Brillio', $manifest['short_name']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertNotEmpty($manifest['shortcuts']);
    }

    #[Test]
    public function service_worker_is_accessible()
    {
        $response = $this->get('/sw.js');

        $response->assertStatus(200);
        $this->assertStringContainsString('brillio-pwa', $response->getContent());
        $this->assertStringContainsString('addEventListener', $response->getContent());
    }

    #[Test]
    public function offline_fallback_page_is_accessible()
    {
        $response = $this->get('/offline.html');

        $response->assertStatus(200);
        $this->assertStringContainsString('hors ligne', $response->getContent());
    }

    #[Test]
    public function security_headers_allow_pwa_workers_and_manifest()
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("worker-src 'self' blob:;", $csp);
        $this->assertStringContainsString("manifest-src 'self';", $csp);
    }

    #[Test]
    public function homepage_includes_pwa_meta_and_install_elements()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('manifest.json', $content);
        $this->assertStringContainsString('viewport-fit=cover', $content);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $content);
        $this->assertStringContainsString('pwa-install-banner', $content);
    }

    #[Test]
    public function login_page_includes_pwa_meta()
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('manifest.json', $content);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $content);
        $this->assertStringContainsString('viewport-fit=cover', $content);
    }

    #[Test]
    public function jeune_space_includes_pwa_meta_and_bottom_nav()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('jeune.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('manifest.json', $content);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $content);
        $this->assertStringContainsString('viewport-fit=cover', $content);
        $this->assertStringContainsString('Navigation principale mobile', $content);
    }

    #[Test]
    public function mentor_space_includes_pwa_meta_and_bottom_nav()
    {
        $user = User::factory()->mentor()->create([
            'onboarding_completed' => true,
        ]);
        MentorProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('mentor.profile'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('manifest.json', $content);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $content);
        $this->assertStringContainsString('viewport-fit=cover', $content);
        $this->assertStringContainsString('Navigation principale mobile mentor', $content);
    }
}

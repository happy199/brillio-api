<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    private const EXTERNAL_BRILLIO_TITLE = 'Ressource Brillio Externe';

    private const INTERNAL_ORG_TITLE = 'Ressource Interne Org';

    private const EXTERNAL_RESOURCE_TITLE = 'Ressource Externe Brillio';

    private const INTERNAL_RESOURCE_TITLE = 'Ressource Interne de Notre Organisation';

    private const INTERNAL_PRIORITY_TITLE = 'Ressource Interne Prioritaire';

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->organization()->create();
        $this->organization = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'contact_email' => $this->admin->email,
            'subscription_plan' => 'pro',
            'anti_competition_enabled' => false,
            'hide_external_resources' => false,
        ]);

        $this->admin->update(['organization_id' => $this->organization->id]);
    }

    protected function getOrgUrl(string $routeName, array $params = []): string
    {
        $url = route($routeName, $params);
        $parsed = parse_url($url);
        $path = ($parsed['path'] ?? '/').(isset($parsed['query']) ? '?'.$parsed['query'] : '');

        return 'https://test-org.brillio.africa'.$path;
    }

    public function test_organization_profile_can_toggle_hide_external_resources(): void
    {
        $this->actingAs($this->admin);

        // 1. Enable toggle
        $response = $this->put($this->getOrgUrl('organization.profile.update'), [
            'name' => $this->organization->name,
            'contact_email' => $this->organization->contact_email,
            'hide_external_resources' => '1',
        ]);

        $response->assertRedirect();
        $this->organization->refresh();
        $this->assertTrue($this->organization->hide_external_resources);

        // 2. Disable toggle
        $response = $this->put($this->getOrgUrl('organization.profile.update'), [
            'name' => $this->organization->name,
            'contact_email' => $this->organization->contact_email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->organization->refresh();
        $this->assertFalse($this->organization->hide_external_resources);
    }

    public function test_organization_admin_can_view_resources_index_and_create_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get($this->getOrgUrl('organization.resources.index'));
        $response->assertStatus(200);
        $response->assertSee('Bibliothèque de Ressources');
        $response->assertSee('Créer une ressource');

        $response = $this->get($this->getOrgUrl('organization.resources.create'));
        $response->assertStatus(200);
        $response->assertSee('Nouvelle Ressource Interne');
    }

    public function test_organization_admin_can_create_internal_resource(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post($this->getOrgUrl('organization.resources.store'), [
            'title' => 'Guide interne de test',
            'type' => 'article',
            'description' => 'Description du guide interne de test',
            'content' => '<p>Contenu exclusif de l\'organisation</p>',
            'is_premium' => '0',
            'is_published' => '1',
        ]);

        $response->assertRedirect($this->getOrgUrl('organization.resources.index', ['tab' => 'internal']));

        $this->assertDatabaseHas('resources', [
            'title' => 'Guide interne de test',
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'is_published' => true,
            'is_validated' => true,
        ]);
    }

    public function test_organization_admin_can_update_and_delete_their_resource(): void
    {
        $this->actingAs($this->admin);

        $resource = Resource::create([
            'user_id' => $this->admin->id,
            'organization_id' => $this->organization->id,
            'title' => 'Ressource initiale',
            'slug' => 'ressource-initiale',
            'type' => 'article',
            'description' => 'Description initiale',
            'content' => 'Contenu initial',
            'is_published' => true,
            'is_validated' => true,
        ]);

        // Edit page
        $response = $this->get($this->getOrgUrl('organization.resources.edit', ['resource' => $resource]));
        $response->assertStatus(200);
        $response->assertSee('Ressource initiale');

        // Update
        $response = $this->put($this->getOrgUrl('organization.resources.update', ['resource' => $resource]), [
            'title' => 'Ressource modifiée',
            'type' => 'article',
            'description' => 'Nouvelle description',
            'content' => 'Nouveau contenu',
            'is_premium' => '0',
            'is_published' => '1',
        ]);

        $response->assertRedirect($this->getOrgUrl('organization.resources.index', ['tab' => 'internal']));
        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'Ressource modifiée',
        ]);

        // Delete
        $response = $this->delete($this->getOrgUrl('organization.resources.destroy', ['resource' => $resource]));
        $response->assertRedirect($this->getOrgUrl('organization.resources.index', ['tab' => 'internal']));
        $this->assertSoftDeleted('resources', [
            'id' => $resource->id,
        ]);
    }

    public function test_organization_admin_cannot_modify_other_organization_or_external_resource(): void
    {
        $this->actingAs($this->admin);

        $otherOrg = Organization::factory()->create(['status' => 'active']);
        $otherResource = Resource::create([
            'user_id' => $this->admin->id,
            'organization_id' => $otherOrg->id,
            'title' => 'Ressource autre organisation',
            'slug' => 'ressource-autre-orga',
            'type' => 'article',
            'description' => 'Autre description',
            'is_published' => true,
            'is_validated' => true,
        ]);

        $response = $this->get($this->getOrgUrl('organization.resources.edit', ['resource' => $otherResource]));
        $response->assertStatus(403);

        $response = $this->put($this->getOrgUrl('organization.resources.update', ['resource' => $otherResource]), [
            'title' => 'Tentative de piratage',
            'type' => 'article',
            'description' => 'Hack description',
            'is_premium' => '0',
        ]);
        $response->assertStatus(403);

        $response = $this->delete($this->getOrgUrl('organization.resources.destroy', ['resource' => $otherResource]));
        $response->assertStatus(403);
    }

    public function test_organization_resource_index_tab_filtering_and_hiding(): void
    {
        $this->actingAs($this->admin);

        $adminUser = User::factory()->create(['is_admin' => true]);

        // External Brillio resource
        Resource::create([
            'user_id' => $adminUser->id,
            'organization_id' => null,
            'title' => self::EXTERNAL_BRILLIO_TITLE,
            'slug' => 'ressource-brillio-externe',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        // Internal resource
        Resource::create([
            'user_id' => $this->admin->id,
            'organization_id' => $this->organization->id,
            'title' => self::INTERNAL_ORG_TITLE,
            'slug' => 'ressource-interne-org',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        // 1. By default (hide_external_resources = false, tab = all): sees both
        $response = $this->get($this->getOrgUrl('organization.resources.index', ['tab' => 'all']));
        $response->assertStatus(200);
        $response->assertSee(self::EXTERNAL_BRILLIO_TITLE);
        $response->assertSee(self::INTERNAL_ORG_TITLE);

        // 2. Tab internal: only sees internal
        $response = $this->get($this->getOrgUrl('organization.resources.index', ['tab' => 'internal']));
        $response->assertStatus(200);
        $response->assertDontSee(self::EXTERNAL_BRILLIO_TITLE);
        $response->assertSee(self::INTERNAL_ORG_TITLE);

        // 3. Tab external: only sees external
        $response = $this->get($this->getOrgUrl('organization.resources.index', ['tab' => 'external']));
        $response->assertStatus(200);
        $response->assertSee(self::EXTERNAL_BRILLIO_TITLE);
        $response->assertDontSee(self::INTERNAL_ORG_TITLE);

        // 4. When hide_external_resources = true:
        $this->organization->update(['hide_external_resources' => true]);

        $response = $this->get($this->getOrgUrl('organization.resources.index', ['tab' => 'all']));
        $response->assertStatus(200);
        $response->assertDontSee(self::EXTERNAL_BRILLIO_TITLE);
        $response->assertSee(self::INTERNAL_ORG_TITLE);
    }

    public function test_youth_in_organization_with_hide_external_resources_sees_only_internal_resources(): void
    {
        $youth = User::factory()->create(['user_type' => 'jeune']);
        $youth->organizations()->attach($this->organization->id);

        $adminUser = User::factory()->create(['is_admin' => true]);

        $externalResource = Resource::create([
            'user_id' => $adminUser->id,
            'organization_id' => null,
            'title' => self::EXTERNAL_RESOURCE_TITLE,
            'slug' => 'ressource-externe-brillio',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        $internalResource = Resource::create([
            'user_id' => $this->admin->id,
            'organization_id' => $this->organization->id,
            'title' => self::INTERNAL_RESOURCE_TITLE,
            'slug' => 'ressource-interne-notre-organisation',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        // Enable hide_external_resources
        $this->organization->update(['hide_external_resources' => true]);
        $youth->refresh();

        $this->assertTrue($youth->hasExternalResourcesHidden());

        $this->actingAs($youth);

        // Youth index should only contain internal resource
        $response = $this->get(route('jeune.resources.index'));
        $response->assertStatus(200);
        $response->assertSee(self::INTERNAL_RESOURCE_TITLE);
        $response->assertDontSee(self::EXTERNAL_RESOURCE_TITLE);

        // Youth show for internal resource succeeds
        $response = $this->get(route('jeune.resources.show', ['resource' => $internalResource]));
        $response->assertStatus(200);
        $response->assertSee(self::INTERNAL_RESOURCE_TITLE);

        // Youth show for external resource is strictly forbidden (404)
        $response = $this->get(route('jeune.resources.show', ['resource' => $externalResource]));
        $response->assertStatus(404);
    }

    public function test_youth_in_organization_without_hide_external_resources_has_organization_source_prioritized(): void
    {
        $youth = User::factory()->create(['user_type' => 'jeune']);
        $youth->organizations()->attach($this->organization->id);

        $adminUser = User::factory()->create(['is_admin' => true]);

        $externalResource = Resource::create([
            'user_id' => $adminUser->id,
            'organization_id' => null,
            'title' => self::EXTERNAL_RESOURCE_TITLE,
            'slug' => 'ressource-externe-brillio',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        Resource::create([
            'user_id' => $this->admin->id,
            'organization_id' => $this->organization->id,
            'title' => self::INTERNAL_PRIORITY_TITLE,
            'slug' => 'ressource-interne-prioritaire',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        $this->assertFalse($youth->hasExternalResourcesHidden());

        $this->actingAs($youth);

        // Youth index: since org has internal resources and no source was specified,
        // it filters to source=organization
        $response = $this->get(route('jeune.resources.index'));
        $response->assertStatus(200);
        $response->assertSee(self::INTERNAL_PRIORITY_TITLE);

        // Youth can still consult external resource when requesting source=all
        $response = $this->get(route('jeune.resources.index', ['source' => 'all']));
        $response->assertStatus(200);
        $response->assertSee(self::EXTERNAL_RESOURCE_TITLE);
        $response->assertSee(self::INTERNAL_PRIORITY_TITLE);

        // Youth show for external resource succeeds because hide_external_resources is false
        $response = $this->get(route('jeune.resources.show', ['resource' => $externalResource]));
        $response->assertStatus(200);
        $response->assertSee(self::EXTERNAL_RESOURCE_TITLE);
    }

    public function test_youth_cannot_access_other_organization_internal_resource(): void
    {
        $youth = User::factory()->create(['user_type' => 'jeune']);
        $youth->organizations()->attach($this->organization->id);

        $otherOrg = Organization::factory()->create(['status' => 'active']);
        $otherAdmin = User::factory()->organization()->create(['organization_id' => $otherOrg->id]);

        $otherOrgResource = Resource::create([
            'user_id' => $otherAdmin->id,
            'organization_id' => $otherOrg->id,
            'title' => 'Secret Resource of Other Org',
            'slug' => 'secret-resource-other-org',
            'type' => 'article',
            'is_published' => true,
            'is_validated' => true,
        ]);

        $this->actingAs($youth);

        // Youth show for other org's resource must return 404
        $response = $this->get(route('jeune.resources.show', ['resource' => $otherOrgResource]));
        $response->assertStatus(404);
    }
}

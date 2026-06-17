<?php

namespace Tests\Feature\Organization;

use App\Models\Advertisement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdvertisementTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PROPOSAL_TITLE = 'New Proposal';

    protected $organization;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->organization()->create();
        $this->organization = Organization::factory()->create([
            'status' => 'active',
            'slug' => 'test-org',
            'contact_email' => $this->admin->email,
            'subscription_plan' => 'free', // Test with lowest standard (free) plan
        ]);

        $this->admin->update(['organization_id' => $this->organization->id]);
    }

    protected function getOrgUrl($routeName, $params = [])
    {
        $url = route($routeName, $params);

        return str_replace('brillio.africa', 'test-org.brillio.africa', $url);
    }

    public function test_organization_admin_can_view_advertisements_index()
    {
        Advertisement::create([
            'title' => 'My Org Ad',
            'image_path' => 'advertisements/org.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.advertisements.index'));

        $response->assertStatus(200);
        $response->assertSee('My Org Ad');
    }

    public function test_organization_admin_can_propose_advertisement()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('ad-visual.png', 800, 600);

        $response = $this->actingAs($this->admin)->post($this->getOrgUrl('organization.advertisements.store'), [
            'title' => self::NEW_PROPOSAL_TITLE,
            'link_url' => 'https://example.com/proposal',
            'image' => $image,
        ]);

        $response->assertRedirect(route('organization.advertisements.index'));

        $this->assertDatabaseHas('advertisements', [
            'title' => self::NEW_PROPOSAL_TITLE,
            'link_url' => 'https://example.com/proposal',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $this->organization->id,
        ]);

        $ad = Advertisement::where('title', self::NEW_PROPOSAL_TITLE)->first();
        $this->assertNotNull($ad);
        // Verify it was converted to webp
        $this->assertStringEndsWith('.webp', $ad->image_path);
        Storage::disk('public')->assertExists($ad->image_path);
    }

    public function test_organization_admin_can_delete_own_advertisement()
    {
        Storage::fake('public');

        $ad = Advertisement::create([
            'title' => 'To Delete',
            'image_path' => 'advertisements/todelete.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $this->organization->id,
        ]);

        Storage::disk('public')->put($ad->image_path, 'fake content');

        $response = $this->actingAs($this->admin)->delete($this->getOrgUrl('organization.advertisements.destroy', $ad));

        $response->assertRedirect(route('organization.advertisements.index'));
        $this->assertDatabaseMissing('advertisements', ['id' => $ad->id]);
        Storage::disk('public')->assertMissing($ad->image_path);
    }

    public function test_organization_admin_cannot_delete_other_organization_advertisement()
    {
        $otherOrg = Organization::factory()->create(['status' => 'active', 'slug' => 'other-org']);

        $ad = Advertisement::create([
            'title' => 'Other Org Ad',
            'image_path' => 'advertisements/other.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $otherOrg->id,
        ]);

        $response = $this->actingAs($this->admin)->delete($this->getOrgUrl('organization.advertisements.destroy', $ad));

        $response->assertStatus(403);
        $this->assertDatabaseHas('advertisements', ['id' => $ad->id]);
    }

    public function test_organization_admin_can_view_edit_page()
    {
        $ad = Advertisement::create([
            'title' => 'Ad to Edit',
            'image_path' => 'advertisements/toedit.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.advertisements.edit', $ad));

        $response->assertStatus(200);
        $response->assertSee('Ad to Edit');
    }

    public function test_organization_admin_can_update_own_advertisement_without_changing_image()
    {
        $ad = Advertisement::create([
            'title' => 'Old Title',
            'image_path' => 'advertisements/old.webp',
            'status' => Advertisement::STATUS_APPROVED,
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->admin)->put($this->getOrgUrl('organization.advertisements.update', $ad), [
            'title' => 'Updated Title',
            'link_url' => 'https://example.com/updated',
        ]);

        $response->assertRedirect(route('organization.advertisements.index'));
        $this->assertDatabaseHas('advertisements', [
            'id' => $ad->id,
            'title' => 'Updated Title',
            'link_url' => 'https://example.com/updated',
            'status' => Advertisement::STATUS_APPROVED,
        ]);
    }

    public function test_organization_admin_cannot_edit_or_update_other_organization_advertisement()
    {
        $otherOrg = Organization::factory()->create(['status' => 'active', 'slug' => 'other-org']);
        $ad = Advertisement::create([
            'title' => 'Other Ad',
            'image_path' => 'advertisements/other.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $otherOrg->id,
        ]);

        $responseGet = $this->actingAs($this->admin)->get($this->getOrgUrl('organization.advertisements.edit', $ad));
        $responseGet->assertStatus(403);

        $responsePut = $this->actingAs($this->admin)->put($this->getOrgUrl('organization.advertisements.update', $ad), [
            'title' => 'Hacked Title',
        ]);
        $responsePut->assertStatus(403);
    }
}

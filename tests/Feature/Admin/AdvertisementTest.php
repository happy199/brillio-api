<?php

namespace Tests\Feature\Admin;

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

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_advertisements_index()
    {
        $organization = Organization::factory()->create();
        $ad = Advertisement::create([
            'title' => 'Test Ad',
            'image_path' => 'advertisements/test.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.advertisements.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Ad');
    }

    public function test_admin_can_create_and_publish_advertisement_directly()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('admin-ad.png', 800, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.advertisements.store'), [
            'title' => 'Admin Direct Ad',
            'link_url' => 'https://example.com/admin',
            'image' => $image,
        ]);

        $response->assertRedirect(route('admin.advertisements.index'));

        $this->assertDatabaseHas('advertisements', [
            'title' => 'Admin Direct Ad',
            'link_url' => 'https://example.com/admin',
            'status' => Advertisement::STATUS_APPROVED,
            'organization_id' => null,
            'created_by' => $this->admin->id,
        ]);

        $ad = Advertisement::where('title', 'Admin Direct Ad')->first();
        $this->assertNotNull($ad);
        $this->assertStringEndsWith('.webp', $ad->image_path);
        Storage::disk('public')->assertExists($ad->image_path);
    }

    public function test_admin_can_approve_pending_advertisement()
    {
        $organization = Organization::factory()->create();
        $ad = Advertisement::create([
            'title' => 'Pending Proposal',
            'image_path' => 'advertisements/pending.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.advertisements.approve', $ad));

        $response->assertRedirect(route('admin.advertisements.index'));

        $this->assertDatabaseHas('advertisements', [
            'id' => $ad->id,
            'status' => Advertisement::STATUS_APPROVED,
            'validated_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_reject_pending_advertisement()
    {
        $organization = Organization::factory()->create();
        $ad = Advertisement::create([
            'title' => 'Pending Proposal',
            'image_path' => 'advertisements/pending.webp',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.advertisements.reject', $ad));

        $response->assertRedirect(route('admin.advertisements.index'));

        $this->assertDatabaseHas('advertisements', [
            'id' => $ad->id,
            'status' => Advertisement::STATUS_REJECTED,
            'validated_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_delete_any_advertisement()
    {
        Storage::fake('public');

        $organization = Organization::factory()->create();
        $ad = Advertisement::create([
            'title' => 'Delete Me',
            'image_path' => 'advertisements/deleteme.webp',
            'status' => Advertisement::STATUS_APPROVED,
            'organization_id' => $organization->id,
        ]);

        Storage::disk('public')->put($ad->image_path, 'fake visual');

        $response = $this->actingAs($this->admin)->delete(route('admin.advertisements.destroy', $ad));

        $response->assertRedirect(route('admin.advertisements.index'));
        $this->assertDatabaseMissing('advertisements', ['id' => $ad->id]);
        Storage::disk('public')->assertMissing($ad->image_path);
    }
}

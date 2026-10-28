<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    use CreatesAdminUser, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_or_regular_user_cannot_access_admin_partners(): void
    {
        $response = $this->get(route('admin.partners.index'));
        $response->assertRedirect(url('/rejoindre'));

        $user = User::factory()->create([
            'user_type' => 'jeune',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.partners.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_partners_index(): void
    {
        $admin = $this->createAdminUser();

        $partner = Partner::create([
            'name' => 'Campus France',
            'website' => 'https://campusfrance.org',
            'logo_path' => 'partners/logos/campus.png',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.partners.index'));

        $response->assertStatus(200);
        $response->assertSee('Partenaires');
        $response->assertSee('Campus France');
    }

    public function test_admin_can_create_an_external_partner(): void
    {
        $admin = $this->createAdminUser();

        $file = UploadedFile::fake()->image('partner_logo.png', 200, 200);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.partners.store'), [
                'name' => 'Partenaire Externe Test',
                'website' => 'https://partner-test.com',
                'logo' => $file,
                'display_order' => 1,
                'type' => 'external',
                'description' => 'Un grand partenaire stratégique',
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.partners.index', ['tab' => 'external']));
        $this->assertDatabaseHas('partners', [
            'name' => 'Partenaire Externe Test',
            'website' => 'https://partner-test.com',
            'is_active' => true,
        ]);

        $partner = Partner::where('name', 'Partenaire Externe Test')->first();
        $this->assertNotNull($partner->logo_path);
        Storage::disk('public')->assertExists($partner->logo_path);
    }

    public function test_admin_can_update_external_partner(): void
    {
        $admin = $this->createAdminUser();

        $partner = Partner::create([
            'name' => 'Ancien Nom',
            'website' => 'https://ancien.com',
            'logo_path' => 'partners/logos/old.png',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->put(route('admin.partners.update', $partner), [
                'name' => 'Nouveau Nom Partenaire',
                'website' => 'https://nouveau.com',
                'display_order' => 5,
                'type' => 'institutionnel',
                'description' => 'Mise à jour',
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.partners.index', ['tab' => 'external']));
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Nouveau Nom Partenaire',
            'website' => 'https://nouveau.com',
        ]);
    }

    public function test_admin_can_toggle_partner_status(): void
    {
        $admin = $this->createAdminUser();

        $partner = Partner::create([
            'name' => 'Partenaire Toggle',
            'website' => 'https://toggle.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->patch(route('admin.partners.toggle-status', $partner));

        $response->assertRedirect();
        $this->assertFalse($partner->fresh()->is_active);

        $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->patch(route('admin.partners.toggle-status', $partner));

        $this->assertTrue($partner->fresh()->is_active);
    }

    public function test_admin_can_delete_partner(): void
    {
        $admin = $this->createAdminUser();

        $file = UploadedFile::fake()->image('delete_me.png');
        $path = $file->store('partners/logos', 'public');

        $partner = Partner::create([
            'name' => 'A Supprimer',
            'logo_path' => $path,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->delete(route('admin.partners.destroy', $partner));

        $response->assertRedirect(route('admin.partners.index', ['tab' => 'external']));
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_about_page_displays_both_brillio_organizations_and_external_partners(): void
    {
        // 1. Create external active partner
        $partner = Partner::create([
            'name' => 'Partenaire Externe Public',
            'website' => 'https://external-public.com',
            'logo_path' => 'partners/logos/public.png',
            'is_active' => true,
        ]);

        // 2. Create inactive external partner
        $inactivePartner = Partner::create([
            'name' => 'Partenaire Cache Inactif',
            'website' => 'https://hidden.com',
            'logo_path' => 'partners/logos/hidden.png',
            'is_active' => false,
        ]);

        // 3. Create active organization with logo
        $org = Organization::create([
            'name' => 'Organisation Brillio Partenaire',
            'contact_email' => 'contact@org-partenaire.com',
            'status' => 'active',
            'logo_url' => 'organizations/logos/org.png',
            'website' => 'https://org-partenaire.com',
            'subscription_plan' => 'pro',
            'private_circle_plus_enabled' => false,
        ]);

        $response = $this->get(route('about'));

        $response->assertStatus(200);
        $response->assertSee('Partenaire Externe Public');
        $response->assertSee('Organisation Brillio Partenaire');
        $response->assertDontSee('Partenaire Cache Inactif');
    }
}

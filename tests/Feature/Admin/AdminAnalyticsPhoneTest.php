<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private const TEST_PHONE = '+22997000000';

    private const USER_WITH_PHONE_NAME = 'User With Phone';

    private const USER_WITHOUT_PHONE_NAME = 'User Without Phone';

    public function test_user_details_displays_phone_number_or_neant()
    {
        // 1. User with phone
        $userWithPhone = User::factory()->create([
            'phone' => self::TEST_PHONE,
            'user_type' => 'jeune',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $userWithPhone));
        $response->assertStatus(200);
        $response->assertSee(self::TEST_PHONE);

        // 2. User without phone
        $userWithoutPhone = User::factory()->create([
            'phone' => null,
            'user_type' => 'jeune',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $userWithoutPhone));
        $response->assertStatus(200);
        $response->assertSee('Néant');
    }

    public function test_analytics_dashboard_contains_phone_stats()
    {
        // Create users
        User::factory()->create(['phone' => self::TEST_PHONE, 'user_type' => 'jeune', 'is_admin' => false]);
        User::factory()->create(['phone' => null, 'user_type' => 'jeune', 'is_admin' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.analytics.index'));
        $response->assertStatus(200);
        $response->assertViewHas('stats');

        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('phone_stats', $stats);
        $this->assertEquals(1, $stats['phone_stats']['with_phone']);
        $this->assertEquals(1, $stats['phone_stats']['without_phone']);
    }

    public function test_csv_export_filters_by_phone_number_presence()
    {
        // Create users
        User::factory()->create(['name' => self::USER_WITH_PHONE_NAME, 'phone' => self::TEST_PHONE, 'user_type' => 'jeune', 'is_admin' => false]);
        User::factory()->create(['name' => self::USER_WITHOUT_PHONE_NAME, 'phone' => null, 'user_type' => 'jeune', 'is_admin' => false]);

        // Filter: has_phone = 1 (Only with phone)
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.export-csv', [
            'type' => 'users',
            'has_phone' => '1',
        ]));
        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString(self::USER_WITH_PHONE_NAME, $content);
        $this->assertStringContainsString(self::TEST_PHONE, $content);
        $this->assertStringNotContainsString(self::USER_WITHOUT_PHONE_NAME, $content);

        // Filter: has_phone = 0 (Only without phone)
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.export-csv', [
            'type' => 'users',
            'has_phone' => '0',
        ]));
        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringNotContainsString(self::USER_WITH_PHONE_NAME, $content);
        $this->assertStringContainsString(self::USER_WITHOUT_PHONE_NAME, $content);
        $this->assertStringContainsString('Néant', $content);
    }

    public function test_pdf_export_filters_by_phone_number_presence()
    {
        // Create users
        User::factory()->create(['name' => self::USER_WITH_PHONE_NAME, 'phone' => self::TEST_PHONE, 'user_type' => 'jeune', 'is_admin' => false]);
        User::factory()->create(['name' => self::USER_WITHOUT_PHONE_NAME, 'phone' => null, 'user_type' => 'jeune', 'is_admin' => false]);

        // Filter: has_phone = 1 (Only with phone)
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.export-pdf', [
            'type' => 'users',
            'has_phone' => '1',
        ]));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Filter: has_phone = 0 (Only without phone)
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.export-pdf', [
            'type' => 'users',
            'has_phone' => '0',
        ]));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}

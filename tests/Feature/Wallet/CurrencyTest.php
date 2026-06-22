<?php

namespace Tests\Feature\Wallet;

use App\Models\MentorProfile;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test rate conversion logic in CurrencyService.
     */
    public function test_currency_conversion()
    {
        $amount = 1000; // XOF

        // Convert XOF to XOF
        $this->assertEquals(1000, CurrencyService::convert($amount, 'XOF', 'XOF'));

        // Convert XOF to XAF (1 XOF = 1.0 XAF)
        $this->assertEquals(1000, CurrencyService::convert($amount, 'XOF', 'XAF'));

        // Convert XOF to GNF (1 XOF = 13.1 GNF)
        $this->assertEqualsWithDelta(13100, CurrencyService::convert($amount, 'XOF', 'GNF'), 0.1);

        // Convert XOF to MAD (1 XOF = 0.01639344 MAD)
        $this->assertEqualsWithDelta(16.39344, CurrencyService::convert($amount, 'XOF', 'MAD'), 0.0001);
    }

    /**
     * Test currency formatting logic in CurrencyService.
     */
    public function test_currency_formatting()
    {
        $amount = 1000;

        // XAF formatting (suffix symbol, 0 decimals, space thousands separator)
        $this->assertEquals('1 000 FCFA', CurrencyService::format($amount, 'XAF'));

        // GNF formatting (suffix symbol, 0 decimals, space thousands separator)
        $this->assertEquals('13 100 FG', CurrencyService::format($amount, 'GNF'));

        // MAD formatting (suffix symbol, 2 decimals, comma separator)
        $this->assertEquals('16,39 MAD', CurrencyService::format($amount, 'MAD'));

        // XOF formatting (suffix symbol, 0 decimals)
        $this->assertEquals('1 000 FCFA', CurrencyService::format($amount, 'XOF'));
    }

    /**
     * Test currency switch route stores session.
     */
    public function test_currency_switch_route()
    {
        $response = $this->get(route('currency.switch', ['currency' => 'MAD']));

        $response->assertRedirect();
        $this->assertEquals('MAD', session('currency'));
    }

    /**
     * Test mentor balance API respects active session currency.
     */
    public function test_mentor_balance_api_respects_session_currency()
    {
        $user = User::factory()->mentor()->create();
        $mentorProfile = MentorProfile::factory()->create([
            'user_id' => $user->id,
            'available_balance' => 65596, // approx 1075 MAD
            'total_withdrawn' => 0,
        ]);

        // Access API with MAD in session
        $response = $this->actingAs($user)
            ->withSession(['currency' => 'MAD'])
            ->getJson('/api/mentor/balance');

        $response->assertStatus(200)
            ->assertJson([
                'currency' => 'MAD',
                'currency_symbol' => 'MAD',
            ]);

        // Check that value is converted (65596 * 0.01639344 = ~1075.34 MAD)
        $data = $response->json();
        $this->assertEqualsWithDelta(1075.34, $data['available_balance'], 0.1);
    }

    /**
     * Test mentor balance API respects currency query parameter.
     */
    public function test_mentor_balance_api_respects_currency_parameter()
    {
        $user = User::factory()->mentor()->create();
        $mentorProfile = MentorProfile::factory()->create([
            'user_id' => $user->id,
            'available_balance' => 65596, // approx 1075 MAD
            'total_withdrawn' => 0,
        ]);

        // Access API with MAD as query parameter
        $response = $this->actingAs($user)
            ->getJson('/api/mentor/balance?currency=MAD');

        $response->assertStatus(200)
            ->assertJson([
                'currency' => 'MAD',
                'currency_symbol' => 'MAD',
            ]);

        // Check that value is converted (65596 * 0.01639344 = ~1075.34 MAD)
        $data = $response->json();
        $this->assertEqualsWithDelta(1075.34, $data['available_balance'], 0.1);
    }
}

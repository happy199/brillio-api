<?php

namespace Tests\Feature\Wallet;

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

        // Convert XOF to EUR (1 XOF = 0.00152449 EUR)
        $this->assertEqualsWithDelta(1.52449, CurrencyService::convert($amount, 'XOF', 'EUR'), 0.0001);

        // Convert XOF to USD (1 XOF = 0.00163934 USD)
        $this->assertEqualsWithDelta(1.63934, CurrencyService::convert($amount, 'XOF', 'USD'), 0.0001);

        // Convert XOF to MAD (1 XOF = 0.01639344 MAD)
        $this->assertEqualsWithDelta(16.39344, CurrencyService::convert($amount, 'XOF', 'MAD'), 0.0001);
    }

    /**
     * Test currency formatting logic in CurrencyService.
     */
    public function test_currency_formatting()
    {
        $amount = 1000;

        // EUR formatting (suffix symbol, 2 decimals, comma separator)
        $this->assertEquals('1,52 €', CurrencyService::format($amount, 'EUR'));

        // USD formatting (prefix symbol, 2 decimals, dot separator)
        $this->assertEquals('$ 1.64', CurrencyService::format($amount, 'USD'));

        // XOF formatting (suffix symbol, 0 decimals)
        $this->assertEquals('1 000 FCFA', CurrencyService::format($amount, 'XOF'));
    }

    /**
     * Test currency switch route stores session.
     */
    public function test_currency_switch_route()
    {
        $response = $this->get(route('currency.switch', ['currency' => 'EUR']));

        $response->assertRedirect();
        $this->assertEquals('EUR', session('currency'));
    }

    /**
     * Test mentor balance API respects active session currency.
     */
    public function test_mentor_balance_api_respects_session_currency()
    {
        $user = User::factory()->mentor()->create();
        $mentorProfile = \App\Models\MentorProfile::factory()->create([
            'user_id' => $user->id,
            'available_balance' => 65596, // approx 100 EUR
            'total_withdrawn' => 0,
        ]);

        // Access API with EUR in session
        $response = $this->actingAs($user)
            ->withSession(['currency' => 'EUR'])
            ->getJson('/api/mentor/balance');

        $response->assertStatus(200)
            ->assertJson([
                'currency' => 'EUR',
                'currency_symbol' => '€',
            ]);

        // Check that value is converted (65596 * 0.00152449 = ~100.00 EUR)
        $data = $response->json();
        $this->assertEqualsWithDelta(100.00, $data['available_balance'], 0.1);
    }
}

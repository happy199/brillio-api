<?php

namespace Tests\Feature\Jeune;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdatePhoneTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PHONE_NUMBER = '+22997000000';

    public function test_json_update_phone_success()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.profile.update'), [
            'phone' => self::TEST_PHONE_NUMBER,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
        ]);

        $user->refresh();
        $this->assertEquals(self::TEST_PHONE_NUMBER, $user->phone);
    }

    public function test_standard_update_phone_redirects()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.profile.update'), [
            'phone' => self::TEST_PHONE_NUMBER,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil mis à jour avec succès.');

        $user->refresh();
        $this->assertEquals(self::TEST_PHONE_NUMBER, $user->phone);
    }
}

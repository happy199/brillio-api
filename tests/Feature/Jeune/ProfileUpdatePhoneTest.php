<?php

namespace Tests\Feature\Jeune;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdatePhoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_update_phone_success()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('jeune.profile.update'), [
            'phone' => '+22997000000',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
        ]);

        $user->refresh();
        $this->assertEquals('+22997000000', $user->phone);
    }

    public function test_standard_update_phone_redirects()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->post(route('jeune.profile.update'), [
            'phone' => '+22997000000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil mis à jour avec succès.');

        $user->refresh();
        $this->assertEquals('+22997000000', $user->phone);
    }
}

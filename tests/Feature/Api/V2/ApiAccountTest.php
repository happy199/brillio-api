<?php

namespace Tests\Feature\Api\V2;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/v2/account/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_can_archive_account()
    {
        $user = User::factory()->create(['is_archived' => false]);

        $response = $this->actingAs($user)->postJson('/api/v2/account/archive');

        $response->assertStatus(200);

        $archivedUser = User::find($user->id);
        $this->assertTrue((bool) $archivedUser->is_archived);
    }
}

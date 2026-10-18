<?php

namespace Tests\Feature\Admin;

use App\Models\User;

trait CreatesAdminUser
{
    protected function createAdminUser(): User
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);

        session(['admin_2fa_passed' => true]);

        return $admin;
    }
}

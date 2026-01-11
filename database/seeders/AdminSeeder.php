<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Crée le compte administrateur par défaut
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Brillio',
            'email' => config('app.admin_email', 'admin@brillio.com'),
            'password' => Hash::make(config('app.admin_password', 'BrillioAdmin2026!')),
            'user_type' => 'jeune', // L'admin peut être de n'importe quel type
            'is_admin' => true,
            'country' => 'Sénégal',
            'city' => 'Dakar',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Compte admin créé : admin@brillio.com');
    }
}

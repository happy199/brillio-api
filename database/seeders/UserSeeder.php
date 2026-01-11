<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crée 10 utilisateurs jeunes de test
     */
    public function run(): void
    {
        $jeunes = [
            [
                'name' => 'Aminata Diallo',
                'email' => 'aminata.diallo@test.com',
                'country' => 'Sénégal',
                'city' => 'Dakar',
                'date_of_birth' => '2005-03-15',
            ],
            [
                'name' => 'Kofi Mensah',
                'email' => 'kofi.mensah@test.com',
                'country' => 'Ghana',
                'city' => 'Accra',
                'date_of_birth' => '2004-07-22',
            ],
            [
                'name' => 'Fatou Ndiaye',
                'email' => 'fatou.ndiaye@test.com',
                'country' => 'Sénégal',
                'city' => 'Saint-Louis',
                'date_of_birth' => '2006-01-10',
            ],
            [
                'name' => 'Emmanuel Okonkwo',
                'email' => 'emmanuel.okonkwo@test.com',
                'country' => 'Nigeria',
                'city' => 'Lagos',
                'date_of_birth' => '2003-11-05',
            ],
            [
                'name' => 'Marie-Claire Abena',
                'email' => 'marie.abena@test.com',
                'country' => 'Cameroun',
                'city' => 'Yaoundé',
                'date_of_birth' => '2005-09-18',
            ],
            [
                'name' => 'Moussa Traoré',
                'email' => 'moussa.traore@test.com',
                'country' => 'Mali',
                'city' => 'Bamako',
                'date_of_birth' => '2004-04-30',
            ],
            [
                'name' => 'Aïssatou Barry',
                'email' => 'aissatou.barry@test.com',
                'country' => 'Guinée',
                'city' => 'Conakry',
                'date_of_birth' => '2006-06-12',
            ],
            [
                'name' => 'Jean-Pierre Nkomo',
                'email' => 'jp.nkomo@test.com',
                'country' => 'RDC',
                'city' => 'Kinshasa',
                'date_of_birth' => '2005-02-28',
            ],
            [
                'name' => 'Fatoumata Coulibaly',
                'email' => 'fatoumata.coulibaly@test.com',
                'country' => 'Côte d\'Ivoire',
                'city' => 'Abidjan',
                'date_of_birth' => '2004-08-14',
            ],
            [
                'name' => 'Ibrahim Keita',
                'email' => 'ibrahim.keita@test.com',
                'country' => 'Mali',
                'city' => 'Bamako',
                'date_of_birth' => '2003-12-20',
            ],
        ];

        foreach ($jeunes as $jeune) {
            User::create([
                'name' => $jeune['name'],
                'email' => $jeune['email'],
                'password' => Hash::make('password123'),
                'user_type' => 'jeune',
                'country' => $jeune['country'],
                'city' => $jeune['city'],
                'date_of_birth' => $jeune['date_of_birth'],
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('10 utilisateurs jeunes créés');
    }
}

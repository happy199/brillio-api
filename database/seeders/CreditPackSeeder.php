<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreditPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider la table pour éviter les doublons lors du re-seeding
        \App\Models\CreditPack::truncate();

        // --- JEUNE (Base 50 FCFA) ---

        // 10 Crédits (Pas de promo) - 500F
        \App\Models\CreditPack::create([
            'user_type' => 'jeune',
            'credits' => 10,
            'price' => 500,
            'promo_percent' => 0,
            'name' => 'Pack Découverte',
            'description' => 'Idéal pour commencer',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 1
        ]);

        // 50 Crédits (-5%) -> 2375 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'jeune',
            'credits' => 50,
            'price' => 2375,
            'promo_percent' => 5,
            'name' => 'Pack Avancé',
            'description' => 'Pour aller plus loin',
            'is_active' => true,
            'is_popular' => true,
            'display_order' => 2
        ]);

        // 100 Crédits (-10%) -> 4500 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'jeune',
            'credits' => 100,
            'price' => 4500,
            'promo_percent' => 10,
            'name' => 'Pack Expert',
            'description' => 'Le meilleur rapport qualité/prix',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 3
        ]);

        // 500 Crédits (-15%) -> 21250 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'jeune',
            'credits' => 500,
            'price' => 21250,
            'promo_percent' => 15,
            'name' => 'Pack Ultime',
            'description' => 'Pour les utilisateurs intensifs',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 4
        ]);


        // --- MENTOR (Base 100 FCFA) ---

        // 10 Crédits (Pas de promo) - 1000F
        \App\Models\CreditPack::create([
            'user_type' => 'mentor',
            'credits' => 10,
            'price' => 1000,
            'promo_percent' => 0,
            'name' => 'Pack Starter',
            'description' => 'Pour tester la plateforme',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 1
        ]);

        // 50 Crédits (-5%) -> 4750 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'mentor',
            'credits' => 50,
            'price' => 4750,
            'promo_percent' => 5,
            'name' => 'Pack Pro',
            'description' => 'Boostez votre visibilité',
            'is_active' => true,
            'is_popular' => true,
            'display_order' => 2
        ]);

        // 100 Crédits (-10%) -> 9000 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'mentor',
            'credits' => 100,
            'price' => 9000,
            'promo_percent' => 10,
            'name' => 'Pack Business',
            'description' => 'Pour les mentors actifs',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 3
        ]);

        // 500 Crédits (-15%) -> 42500 FCFA
        \App\Models\CreditPack::create([
            'user_type' => 'mentor',
            'credits' => 500,
            'price' => 42500,
            'promo_percent' => 15,
            'name' => 'Pack Elite',
            'description' => 'Une visibilité maximale',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 4
        ]);
    }
}

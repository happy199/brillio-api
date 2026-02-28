<?php

namespace Database\Seeders;

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
            'display_order' => 1,
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
            'display_order' => 2,
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
            'display_order' => 3,
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
            'display_order' => 4,
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
            'display_order' => 1,
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
            'display_order' => 2,
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
            'display_order' => 3,
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
            'display_order' => 4,
        ]);

        // --- ORGANIZATION ---

        // Pack Initiation - 50 Crédits - 5000F
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'credits',
            'credits' => 50,
            'price' => 5000,
            'promo_percent' => 0,
            'name' => 'Pack Initiation',
            'description' => 'Pack de 50 crédits pour organisation.',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 1,
        ]);

        // Pack Découverte - 100 Crédits - 9500F
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'credits',
            'credits' => 100,
            'price' => 9500,
            'promo_percent' => 5,
            'name' => 'Pack Découverte',
            'description' => 'Pack de 100 crédits pour organisation.',
            'is_active' => true,
            'is_popular' => true,
            'display_order' => 2,
        ]);

        // Pack Croissance - 250 Crédits - 22500F
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'credits',
            'credits' => 250,
            'price' => 22500,
            'promo_percent' => 10,
            'name' => 'Pack Croissance',
            'description' => 'Pack de 250 crédits pour organisation.',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 3,
        ]);

        // Pack Expansion - 500 Crédits - 42500F
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'credits',
            'credits' => 500,
            'price' => 42500,
            'promo_percent' => 15,
            'name' => 'Pack Expansion',
            'description' => 'Pack de 500 crédits pour organisation.',
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 4,
        ]);

        // --- ORGANIZATION SUBSCRIPTIONS ---

        $proFeatures = [
            'Gestion de liste des jeunes et mentors',
            'Statistiques détaillées (Engagement)',
            'Calendrier global des séances',
            'Suivi des statuts de séances',
            'Exports PDF & CSV',
        ];

        $enterpriseFeatures = [
            'Tout du plan Pro inclus',
            'Marque Blanche (Logo & Couleurs)',
            'Sous-domaine personnalisé',
            'Centre d\'Export (PDF, Excel, CSV)',
            'Support dédié prioritaire',
            '50 Crédits/mois offerts automatiquement',
        ];

        // PRO — Mensuel (30 jours — 20 000 FCFA)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Pro Mensuel',
            'description' => 'Plan Professionnel — 1 mois',
            'target_plan' => 'pro',
            'duration_days' => 30,
            'price' => 20000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $proFeatures,
            'is_active' => true,
            'is_popular' => true,
            'display_order' => 10,
        ]);

        // PRO — 3 mois (90 jours — 60 000 FCFA)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Pro 3 Mois',
            'description' => 'Plan Professionnel — 3 mois',
            'target_plan' => 'pro',
            'duration_days' => 90,
            'price' => 60000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $proFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 11,
        ]);

        // PRO — 6 mois (180 jours — 100 000 FCFA, soit 1 mois offert)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Pro 6 Mois',
            'description' => 'Plan Professionnel — 6 mois (1 mois offert)',
            'target_plan' => 'pro',
            'duration_days' => 180,
            'price' => 100000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $proFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 12,
        ]);

        // PRO — 9 mois (270 jours — 160 000 FCFA, soit 1 mois offert)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Pro 9 Mois',
            'description' => 'Plan Professionnel — 9 mois (1 mois offert)',
            'target_plan' => 'pro',
            'duration_days' => 270,
            'price' => 160000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $proFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 13,
        ]);

        // PRO — Annuel (365 jours — 200 000 FCFA, soit 2 mois offerts)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Pro Annuel',
            'description' => 'Plan Professionnel — 1 an (2 mois offerts)',
            'target_plan' => 'pro',
            'duration_days' => 365,
            'price' => 200000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $proFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 14,
        ]);

        // ENTERPRISE — Mensuel (30 jours — 50 000 FCFA)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Entreprise Mensuel',
            'description' => 'Plan Entreprise — 1 mois',
            'target_plan' => 'enterprise',
            'duration_days' => 30,
            'price' => 50000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $enterpriseFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 20,
        ]);

        // ENTERPRISE — 3 mois (90 jours — 150 000 FCFA)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Entreprise 3 Mois',
            'description' => 'Plan Entreprise — 3 mois',
            'target_plan' => 'enterprise',
            'duration_days' => 90,
            'price' => 150000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $enterpriseFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 21,
        ]);

        // ENTERPRISE — 6 mois (180 jours — 250 000 FCFA, soit 1 mois offert)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Entreprise 6 Mois',
            'description' => 'Plan Entreprise — 6 mois (1 mois offert)',
            'target_plan' => 'enterprise',
            'duration_days' => 180,
            'price' => 250000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $enterpriseFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 22,
        ]);

        // ENTERPRISE — 9 mois (270 jours — 400 000 FCFA, soit 1 mois offert)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Entreprise 9 Mois',
            'description' => 'Plan Entreprise — 9 mois (1 mois offert)',
            'target_plan' => 'enterprise',
            'duration_days' => 270,
            'price' => 400000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $enterpriseFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 23,
        ]);

        // ENTERPRISE — Annuel (365 jours — 500 000 FCFA, soit 2 mois offerts)
        \App\Models\CreditPack::create([
            'user_type' => 'organization',
            'type' => 'subscription',
            'name' => 'Entreprise Annuel',
            'description' => 'Plan Entreprise — 1 an (2 mois offerts)',
            'target_plan' => 'enterprise',
            'duration_days' => 365,
            'price' => 500000,
            'promo_percent' => 0,
            'credits' => 0,
            'features' => $enterpriseFeatures,
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 24,
        ]);
    }
}

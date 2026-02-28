<?php

namespace Database\Seeders;

use App\Models\CreditPack;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Standard Plan (Free)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_FREE,
                'user_type' => 'organization',
                'type' => 'subscription',
            ],
            [
                'name' => 'Standard',
                'price' => 0,
                'credits' => 0,
                'duration_days' => 0, // Indefinite
                'description' => 'Pour démarrer et parrainer sans limite.',
                'features' => [
                    'Parrainage illimité de jeunes',
                    'Tableau de bord standard',
                    'Offre de ressources (Gratuit)',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 0,
            ]
        );

        $proFeatures = [
            'Statistiques détaillées',
            'Calendrier global',
            'Suivi des séances',
            'Support prioritaire',
        ];

        // 1. Pro Plan (Monthly)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_PRO,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name' => 'Pro Mensuel',
                'price' => 20000,
                'credits' => 0,
                'description' => 'Plan Professionnel — 1 mois',
                'features' => $proFeatures,
                'is_active' => true,
                'is_popular' => true,
                'display_order' => 10,
            ]
        );

        // 2. Pro Plan (3 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_PRO,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 90,
            ],
            [
                'name' => 'Pro 3 Mois',
                'price' => 60000,
                'credits' => 0,
                'description' => 'Plan Professionnel — 3 mois',
                'features' => $proFeatures,
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 11,
            ]
        );

        // 3. Pro Plan (6 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_PRO,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 180,
            ],
            [
                'name' => 'Pro 6 Mois',
                'price' => 100000, // 1 month free
                'credits' => 0,
                'description' => 'Plan Professionnel — 6 mois (1 mois offert)',
                'features' => [
                    ...$proFeatures,
                    '1 mois offert',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 12,
            ]
        );

        // 4. Pro Plan (9 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_PRO,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 270,
            ],
            [
                'name' => 'Pro 9 Mois',
                'price' => 160000, // 1 month free implicitly assuming 20k/mo
                'credits' => 0,
                'description' => 'Plan Professionnel — 9 mois (1 mois offert)',
                'features' => [
                    ...$proFeatures,
                    '1 mois offert',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 13,
            ]
        );

        // 5. Pro Plan (Yearly)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_PRO,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 365,
            ],
            [
                'name' => 'Pro Annuel',
                'price' => 200000, // 2 months free
                'credits' => 0,
                'description' => 'Plan Professionnel — 1 an (2 mois offerts)',
                'features' => [
                    ...$proFeatures,
                    '2 mois offerts',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 14,
            ]
        );

        $enterpriseFeatures = [
            'Marque Blanche (Logo & Couleurs)',
            'Nom de domaine personnalisé',
            'Centre d\'Export',
            'Support dédié',
            '50 Crédits/mois offerts',
        ];

        // 6. Enterprise Plan (Monthly)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ENTERPRISE,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name' => 'Entreprise Mensuel',
                'price' => 50000,
                'credits' => 50,
                'description' => 'Plan Entreprise — 1 mois',
                'features' => $enterpriseFeatures,
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 20,
            ]
        );

        // 7. Enterprise Plan (3 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ENTERPRISE,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 90,
            ],
            [
                'name' => 'Entreprise 3 Mois',
                'price' => 150000,
                'credits' => 150,
                'description' => 'Plan Entreprise — 3 mois',
                'features' => $enterpriseFeatures,
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 21,
            ]
        );

        // 8. Enterprise Plan (6 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ENTERPRISE,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 180,
            ],
            [
                'name' => 'Entreprise 6 Mois',
                'price' => 250000, // 1 month free
                'credits' => 300,
                'description' => 'Plan Entreprise — 6 mois (1 mois offert)',
                'features' => [
                    ...$enterpriseFeatures,
                    '1 mois offert',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 22,
            ]
        );

        // 9. Enterprise Plan (9 Months)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ENTERPRISE,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 270,
            ],
            [
                'name' => 'Entreprise 9 Mois',
                'price' => 400000, // 1 month free
                'credits' => 450,
                'description' => 'Plan Entreprise — 9 mois (1 mois offert)',
                'features' => [
                    ...$enterpriseFeatures,
                    '1 mois offert',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 23,
            ]
        );

        // 10. Enterprise Plan (Yearly)
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ENTERPRISE,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 365,
            ],
            [
                'name' => 'Entreprise Annuel',
                'price' => 500000, // 2 months free
                'credits' => 600,
                'description' => 'Plan Entreprise — 1 an (2 mois offerts)',
                'features' => [
                    ...$enterpriseFeatures,
                    '2 mois offerts',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 24,
            ]
        );
    }
}

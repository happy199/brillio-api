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
        // 1. Pro Plan (Monthly)
        CreditPack::updateOrCreate(
        [
            'name' => 'Abonnement Pro (Mensuel)',
            'user_type' => 'organization',
            'type' => 'subscription',
            'duration_days' => 30,
        ],
        [
            'target_plan' => Organization::PLAN_PRO,
            'price' => 20000,
            'credits' => 0,
            'description' => 'Statistiques détaillées, calendrier global, suivi des séances.',
            'features' => [
                'Statistiques détaillées',
                'Calendrier global',
                'Suivi des séances',
                'Support prioritaire'
            ],
            'is_active' => true,
            'is_popular' => true,
            'display_order' => 1
        ]
        );

        // 2. Pro Plan (Yearly)
        CreditPack::updateOrCreate(
        [
            'name' => 'Abonnement Pro (Annuel)',
            'user_type' => 'organization',
            'type' => 'subscription',
            'duration_days' => 365,
        ],
        [
            'target_plan' => Organization::PLAN_PRO,
            'price' => 200000, // 2 months free
            'credits' => 0,
            'description' => 'Statistiques détaillées, calendrier global, suivi des séances. (2 mois offerts)',
            'features' => [
                'Tout du plan Mensuel',
                '2 mois offerts'
            ],
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 2
        ]
        );

        // 3. Enterprise Plan (Monthly)
        CreditPack::updateOrCreate(
        [
            'name' => 'Abonnement Entreprise (Mensuel)',
            'user_type' => 'organization',
            'type' => 'subscription',
            'duration_days' => 30,
        ],
        [
            'target_plan' => Organization::PLAN_ENTERPRISE,
            'price' => 50000,
            'credits' => 50, // 50 credits offered per month
            'description' => 'Support dédié, exports complets, 50 crédits offerts.',
            'features' => [
                'Tout du plan Pro',
                'Centre d\'Export',
                'Support dédié',
                '50 Crédits/mois offerts'
            ],
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 3
        ]
        );

        // 4. Enterprise Plan (Yearly)
        CreditPack::updateOrCreate(
        [
            'name' => 'Abonnement Entreprise (Annuel)',
            'user_type' => 'organization',
            'type' => 'subscription',
            'duration_days' => 365,
        ],
        [
            'target_plan' => Organization::PLAN_ENTERPRISE,
            'price' => 500000, // 2 months free
            'credits' => 600, // 50 * 12
            'description' => 'Support dédié, exports complets, crédits inclus. (2 mois offerts)',
            'features' => [
                'Tout du plan Mensuel',
                '2 mois offerts'
            ],
            'is_active' => true,
            'is_popular' => false,
            'display_order' => 4
        ]
        );
    }
}
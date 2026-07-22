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
        // ─────────────────────────────────────────────
        // 0. Plan Standard (Gratuit) — 10 membres max
        // ─────────────────────────────────────────────
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_FREE,
                'user_type'   => 'organization',
                'type'        => 'subscription',
            ],
            [
                'name'          => 'Standard',
                'price'         => 0,
                'credits'       => 0,
                'duration_days' => 0, // Indéfini
                'member_limit'  => 10,
                'description'   => 'Pour démarrer et parrainer vos premiers membres.',
                'features'      => [
                    "Jusqu'à 10 membres (jeunes + mentors)",
                    'Tableau de bord standard',
                    'Offre de ressources (Gratuit)',
                    "Liens d'invitation partageable",
                ],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 0,
            ]
        );

        // ─────────────────────────────────────────────
        // Plans PRO — 20 membres max
        // ─────────────────────────────────────────────
        $proFeatures = [
            "Jusqu'à 20 membres (jeunes + mentors)",
            'Gestion de liste des jeunes et mentors',
            'Statistiques détaillées (Engagement)',
            'Calendrier global des séances',
            'Suivi des statuts de séances',
            'Exports PDF & CSV',
            'Support prioritaire',
        ];

        // 1. Pro Mensuel
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_PRO,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name'          => 'Pro Mensuel',
                'price'         => 20000,
                'credits'       => 0,
                'member_limit'  => 20,
                'description'   => 'Plan Professionnel — 1 mois',
                'features'      => $proFeatures,
                'is_active'     => true,
                'is_popular'    => true,
                'display_order' => 10,
            ]
        );

        // 2. Pro 3 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_PRO,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 90,
            ],
            [
                'name'          => 'Pro 3 Mois',
                'price'         => 60000,
                'credits'       => 0,
                'member_limit'  => 20,
                'description'   => 'Plan Professionnel — 3 mois',
                'features'      => $proFeatures,
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 11,
            ]
        );

        $oneMonthOffered = '1 mois offert';
        $twoMonthsOffered = '2 mois offerts';

        // 3. Pro 6 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_PRO,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 180,
            ],
            [
                'name'          => 'Pro 6 Mois',
                'price'         => 100000,
                'credits'       => 0,
                'member_limit'  => 20,
                'description'   => 'Plan Professionnel — 6 mois (' . $oneMonthOffered . ')',
                'features'      => [...$proFeatures, $oneMonthOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 12,
            ]
        );

        // 4. Pro 9 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_PRO,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 270,
            ],
            [
                'name'          => 'Pro 9 Mois',
                'price'         => 160000,
                'credits'       => 0,
                'member_limit'  => 20,
                'description'   => 'Plan Professionnel — 9 mois (' . $oneMonthOffered . ')',
                'features'      => [...$proFeatures, $oneMonthOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 13,
            ]
        );

        // 5. Pro Annuel
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_PRO,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 365,
            ],
            [
                'name'          => 'Pro Annuel',
                'price'         => 200000,
                'credits'       => 0,
                'member_limit'  => 20,
                'description'   => 'Plan Professionnel — 1 an (' . $twoMonthsOffered . ')',
                'features'      => [...$proFeatures, $twoMonthsOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 14,
            ]
        );

        // ─────────────────────────────────────────────
        // Plans ENTREPRISE — 50 membres max
        // ─────────────────────────────────────────────
        $enterpriseFeatures = [
            "Jusqu'à 50 membres (jeunes + mentors)",
            'Tout du plan Pro',
            'Marque Blanche (Logo & Couleurs)',
            'Sous-domaine personnalisé',
            'Centre d\'Export de rapports',
            'Support dédié prioritaire',
            '★ 50 Crédits/mois offerts automatiquement',
        ];

        // 6. Entreprise Mensuel
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ENTERPRISE,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name'          => 'Entreprise Mensuel',
                'price'         => 50000,
                'credits'       => 50,
                'member_limit'  => 50,
                'description'   => 'Plan Entreprise — 1 mois',
                'features'      => $enterpriseFeatures,
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 20,
            ]
        );

        // 7. Entreprise 3 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ENTERPRISE,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 90,
            ],
            [
                'name'          => 'Entreprise 3 Mois',
                'price'         => 150000,
                'credits'       => 150,
                'member_limit'  => 50,
                'description'   => 'Plan Entreprise — 3 mois',
                'features'      => $enterpriseFeatures,
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 21,
            ]
        );

        // 8. Entreprise 6 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ENTERPRISE,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 180,
            ],
            [
                'name'          => 'Entreprise 6 Mois',
                'price'         => 250000,
                'credits'       => 300,
                'member_limit'  => 50,
                'description'   => 'Plan Entreprise — 6 mois (' . $oneMonthOffered . ')',
                'features'      => [...$enterpriseFeatures, $oneMonthOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 22,
            ]
        );

        // 9. Entreprise 9 Mois
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ENTERPRISE,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 270,
            ],
            [
                'name'          => 'Entreprise 9 Mois',
                'price'         => 400000,
                'credits'       => 450,
                'member_limit'  => 50,
                'description'   => 'Plan Entreprise — 9 mois (' . $oneMonthOffered . ')',
                'features'      => [...$enterpriseFeatures, $oneMonthOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 23,
            ]
        );

        // 10. Entreprise Annuel
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ENTERPRISE,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 365,
            ],
            [
                'name'          => 'Entreprise Annuel',
                'price'         => 500000,
                'credits'       => 600,
                'member_limit'  => 50,
                'description'   => 'Plan Entreprise — 1 an (' . $twoMonthsOffered . ')',
                'features'      => [...$enterpriseFeatures, $twoMonthsOffered],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 24,
            ]
        );

        // ─────────────────────────────────────────────
        // 11. Plan ÉTABLISSEMENT — Membres illimités
        // ─────────────────────────────────────────────
        CreditPack::updateOrCreate(
            [
                'target_plan'   => Organization::PLAN_ESTABLISHMENT,
                'user_type'     => 'organization',
                'type'          => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name'          => 'Établissement',
                'price'         => 0,
                'credits'       => 0,
                'member_limit'  => null, // Illimité
                'description'   => 'Le plan ultime pour l\'éducation et les centres de formation.',
                'features'      => [
                    'Membres illimités (jeunes + mentors)',
                    'Tout du plan Entreprise',
                    'Fiche Établissement premium personnalisée',
                    'Outils de prospection & ciblage MBTI',
                    'Formulaires d\'intérêt IA (Questions dynamiques)',
                    'Manifestations d\'intérêt illimitées',
                    'Publication d\'événements à la communauté',
                    'Mise en avant prioritaire dans les recherches',
                ],
                'is_active'     => true,
                'is_popular'    => false,
                'display_order' => 30,
            ]
        );
    }
}

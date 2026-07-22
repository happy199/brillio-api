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
        // 0. Plan Standard (Gratuit) — 10 membres max
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
                'duration_days' => 0,
                'member_limit' => 10,
                'description' => 'Pour démarrer et parrainer vos premiers membres.',
                'features' => [
                    "Jusqu'à 10 membres (jeunes + mentors)",
                    'Tableau de bord standard',
                    'Offre de ressources (Gratuit)',
                    "Liens d'invitation partageable",
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 0,
            ]
        );

        // Plans PRO — 20 membres max
        $this->createTieredPlans(
            Organization::PLAN_PRO,
            'Pro',
            'Plan Professionnel',
            20000,
            0,
            20,
            [
                "Jusqu'à 20 membres (jeunes + mentors)",
                'Gestion de liste des jeunes et mentors',
                'Statistiques détaillées (Engagement)',
                'Calendrier global des séances',
                'Suivi des statuts de séances',
                'Exports PDF & CSV',
                'Support prioritaire',
            ],
            10
        );

        // Plans ENTREPRISE — 50 membres max
        $this->createTieredPlans(
            Organization::PLAN_ENTERPRISE,
            'Entreprise',
            'Plan Entreprise',
            50000,
            50,
            50,
            [
                "Jusqu'à 50 membres (jeunes + mentors)",
                'Tout du plan Pro',
                'Marque Blanche (Logo & Couleurs)',
                'Sous-domaine personnalisé',
                'Centre d\'Export de rapports',
                'Support dédié prioritaire',
                '★ 50 Crédits/mois offerts automatiquement',
            ],
            20
        );

        // Plan ÉTABLISSEMENT — Membres illimités
        CreditPack::updateOrCreate(
            [
                'target_plan' => Organization::PLAN_ESTABLISHMENT,
                'user_type' => 'organization',
                'type' => 'subscription',
                'duration_days' => 30,
            ],
            [
                'name' => 'Établissement',
                'price' => 0,
                'credits' => 0,
                'member_limit' => null,
                'description' => 'Le plan ultime pour l\'éducation et les centres de formation.',
                'features' => [
                    'Membres illimités (jeunes + mentors)',
                    'Tout du plan Entreprise',
                    'Fiche Établissement premium personnalisée',
                    'Outils de prospection & ciblage MBTI',
                    'Formulaires d\'intérêt IA (Questions dynamiques)',
                    'Manifestations d\'intérêt illimitées',
                    'Publication d\'événements à la communauté',
                    'Mise en avant prioritaire dans les recherches',
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 30,
            ]
        );
    }

    /**
     * Helper to create multi-duration tiered plans without code duplication.
     */
    private function createTieredPlans(
        string $planCode,
        string $label,
        string $descriptionPrefix,
        int $monthlyPrice,
        int $monthlyCredits,
        int $memberLimit,
        array $baseFeatures,
        int $baseOrder
    ): void {
        $durations = [
            [30, 'Mensuel', 1, 1, null],
            [90, '3 Mois', 3, 3, null],
            [180, '6 Mois', 6, 5, '1 mois offert'],
            [270, '9 Mois', 9, 8, '1 mois offert'],
            [365, 'Annuel', 12, 10, '2 mois offerts'],
        ];

        foreach ($durations as $index => [$days, $periodName, $months, $multiplier, $bonus]) {
            $desc = "$descriptionPrefix — ".($days === 365 ? '1 an' : "$months mois");
            $features = $baseFeatures;

            if ($bonus !== null) {
                $desc .= " ($bonus)";
                $features[] = $bonus;
            }

            CreditPack::updateOrCreate(
                [
                    'target_plan' => $planCode,
                    'user_type' => 'organization',
                    'type' => 'subscription',
                    'duration_days' => $days,
                ],
                [
                    'name' => "$label $periodName",
                    'price' => $monthlyPrice * $multiplier,
                    'credits' => $monthlyCredits * $months,
                    'member_limit' => $memberLimit,
                    'description' => $desc,
                    'features' => $features,
                    'is_active' => true,
                    'is_popular' => ($planCode === Organization::PLAN_PRO && $days === 30),
                    'display_order' => $baseOrder + $index,
                ]
            );
        }
    }
}

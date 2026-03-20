<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Prix des crédits
            [
                'key' => 'credit_price_jeune',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit pour un Jeune (FCFA)',
            ],
            [
                'key' => 'credit_price_mentor',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit pour un Mentor (FCFA)',
            ],
            [
                'key' => 'credit_price_organization',
                'value' => '150',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit pour une Organisation (FCFA)',
            ],

            // Coûts des fonctionnalités
            [
                'key' => 'feature_cost_analysis_tool',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour utiliser l\'outil d\'analyse de la demande',
            ],
            [
                'key' => 'feature_cost_advanced_targeting',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour le ciblage avancé des ressources',
            ],
            [
                'key' => 'feature_cost_contact_advisor',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour contacter un conseiller humain',
            ],
            [
                'key' => 'feature_cost_new_chat',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour créer une nouvelle conversation IA',
            ],
            [
                'key' => 'feature_cost_unlock_history',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour débloquer l\'historique complet des séances',
            ],
            [
                'key' => 'feature_cost_compiled_report',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Nombre de crédits pour générer un rapport compilé',
            ],

            // Commissions et frais
            [
                'key' => 'mentorship_commission_percent',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Commission Mise en Relation (%)',
            ],
            [
                'key' => 'payout_fee_percentage',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Commission Retrait Mentor (%)',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

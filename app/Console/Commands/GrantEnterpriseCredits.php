<?php

namespace App\Console\Commands;

use App\Models\Organization;

class GrantEnterpriseCredits extends GrantOrganizationCreditsCommand
{
    protected $signature = 'organizations:grant-enterprise-credits';

    protected $description = 'Distribue automatiquement les crédits gratuits aux organisations abonnées au plan Entreprise';

    protected function getTargetPlan(): string
    {
        return Organization::PLAN_ENTERPRISE;
    }

    protected function getSettingKey(): string
    {
        return 'credit_bonus_enterprise';
    }

    protected function getDefaultBonus(): int
    {
        return 50;
    }

    protected function getPlanDisplayName(): string
    {
        return 'Enterprise';
    }
}

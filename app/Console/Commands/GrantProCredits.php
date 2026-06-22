<?php

namespace App\Console\Commands;

use App\Models\Organization;

class GrantProCredits extends GrantOrganizationCreditsCommand
{
    protected $signature = 'organizations:grant-pro-credits';

    protected $description = 'Distribue automatiquement les crédits gratuits aux organisations abonnées au plan Pro';

    protected function getTargetPlan(): string
    {
        return Organization::PLAN_PRO;
    }

    protected function getSettingKey(): string
    {
        return 'credit_bonus_pro';
    }

    protected function getDefaultBonus(): int
    {
        return 25;
    }

    protected function getPlanDisplayName(): string
    {
        return 'Pro';
    }
}

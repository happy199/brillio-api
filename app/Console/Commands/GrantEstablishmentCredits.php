<?php

namespace App\Console\Commands;

use App\Models\Organization;

class GrantEstablishmentCredits extends GrantOrganizationCreditsCommand
{
    protected $signature = 'organizations:grant-establishment-credits';

    protected $description = 'Distribue automatiquement les crédits gratuits aux organisations abonnées au plan Établissement';

    protected function getTargetPlan(): string
    {
        return Organization::PLAN_ESTABLISHMENT;
    }

    protected function getSettingKey(): string
    {
        return 'credit_bonus_establishment';
    }

    protected function getDefaultBonus(): int
    {
        return 50;
    }

    protected function getPlanDisplayName(): string
    {
        return 'Établissement';
    }
}

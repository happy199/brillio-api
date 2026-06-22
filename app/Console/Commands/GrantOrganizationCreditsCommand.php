<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\SystemSetting;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

abstract class GrantOrganizationCreditsCommand extends Command
{
    public function __construct(protected WalletService $walletService)
    {
        parent::__construct();
    }

    /**
     * Get the organization plan this command targets (e.g. Organization::PLAN_PRO).
     */
    abstract protected function getTargetPlan(): string;

    /**
     * Get the setting key for the credit bonus amount (e.g. 'credit_bonus_pro').
     */
    abstract protected function getSettingKey(): string;

    /**
     * Get the default credit bonus amount.
     */
    abstract protected function getDefaultBonus(): int;

    /**
     * Get the display name of the plan for logs and messages (e.g. 'Pro', 'Enterprise').
     */
    abstract protected function getPlanDisplayName(): string;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $planName = $this->getPlanDisplayName();
        $this->info("Distribution des crédits {$planName} mensuels...");

        $creditBonus = SystemSetting::getValue($this->getSettingKey(), $this->getDefaultBonus());

        $orgs = Organization::where('subscription_plan', $this->getTargetPlan())
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '>', now())
            ->get();

        if ($orgs->isEmpty()) {
            $this->info("Aucune organisation {$planName} active trouvée.");

            return 0;
        }

        $month = now()->translatedFormat('F Y');
        $success = 0;
        $errors = 0;

        foreach ($orgs as $organization) {
            try {
                $this->walletService->addCredits(
                    $organization,
                    $creditBonus,
                    'bonus',
                    "{$creditBonus} crédits offerts — Plan {$planName} ({$month})"
                );

                Log::info("{$planName} credits granted", [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'credits' => $creditBonus,
                    'month' => $month,
                ]);

                $this->line("  ✓ {$organization->name} : +{$creditBonus} crédits");
                $success++;
            } catch (\Exception $e) {
                Log::error("Erreur distribution crédits {$planName}", [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'error' => $e->getMessage(),
                ]);

                $this->warn("  ✗ Échec pour {$organization->name} : {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Terminé : {$success} succès, {$errors} erreurs.");

        return 0;
    }
}

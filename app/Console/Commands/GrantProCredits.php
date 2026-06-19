<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\SystemSetting;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantProCredits extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'organizations:grant-pro-credits';

    /**
     * The console command description.
     */
    protected $description = 'Distribue automatiquement les crédits gratuits aux organisations abonnées au plan Pro';

    public function __construct(protected WalletService $walletService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Distribution des crédits Pro mensuels...');

        $creditBonus = SystemSetting::getValue('credit_bonus_pro', 25);

        $proOrgs = Organization::where('subscription_plan', Organization::PLAN_PRO)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '>', now())
            ->get();

        if ($proOrgs->isEmpty()) {
            $this->info('Aucune organisation Pro active trouvée.');

            return 0;
        }

        $month = now()->translatedFormat('F Y');
        $success = 0;
        $errors = 0;

        foreach ($proOrgs as $organization) {
            try {
                $this->walletService->addCredits(
                    $organization,
                    $creditBonus,
                    'bonus',
                    "{$creditBonus} crédits offerts — Plan Pro ({$month})"
                );

                Log::info('Pro credits granted', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'credits' => $creditBonus,
                    'month' => $month,
                ]);

                $this->line("  ✓ {$organization->name} : +{$creditBonus} crédits");
                $success++;
            } catch (\Exception $e) {
                Log::error('Erreur distribution crédits Pro', [
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

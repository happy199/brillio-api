<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantEnterpriseCredits extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'organizations:grant-enterprise-credits';

    /**
     * The console command description.
     */
    protected $description = 'Distribue automatiquement 50 crédits gratuits aux organisations abonnées au plan Entreprise';

    public function __construct(protected WalletService $walletService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Distribution des crédits Enterprise mensuels...');

        $enterpriseOrgs = Organization::where('subscription_plan', Organization::PLAN_ENTERPRISE)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '>', now())
            ->get();

        if ($enterpriseOrgs->isEmpty()) {
            $this->info('Aucune organisation Enterprise active trouvée.');

            return 0;
        }

        $month = now()->translatedFormat('F Y');
        $success = 0;
        $errors = 0;

        foreach ($enterpriseOrgs as $organization) {
            try {
                $this->walletService->addCredits(
                    $organization,
                    50,
                    'bonus',
                    "50 crédits offerts — Plan Entreprise ({$month})"
                );

                Log::info('Enterprise credits granted', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'credits' => 50,
                    'month' => $month,
                ]);

                $this->line("  ✓ {$organization->name} : +50 crédits");
                $success++;
            } catch (\Exception $e) {
                Log::error('Erreur distribution crédits Enterprise', [
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

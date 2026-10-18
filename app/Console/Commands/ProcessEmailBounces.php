<?php

namespace App\Console\Commands;

use App\Services\BounceProcessingService;
use Illuminate\Console\Command;

class ProcessEmailBounces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-bounces'
        .' {--file= : Chemin vers un fichier contenant des retours de rebond NDR}'
        .' {--scan-domains : Vérifie également la validité DNS MX des adresses utilisateurs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse les retours d\'erreurs SMTP (NDR / Bounces) et exclut les adresses rejetées pour protéger la réputation Namecheap';

    /**
     * Execute the console command.
     */
    public function handle(BounceProcessingService $service): int
    {
        $this->info('Démarrage de l\'analyse des rejets et protection anti-rebond...');

        $filePath = $this->option('file');
        if ($filePath && file_exists($filePath)) {
            $this->info("Lecture du fichier : {$filePath}");
            $content = file_get_contents($filePath);
            $result = $service->parseBounceText($content);

            $this->info("Analyse terminée : {$result['total_found']} adresses traitées ({$result['hard_bounced']} rejets définitifs, {$result['soft_bounced']} temporaires).");
            foreach ($result['details'] as $detail) {
                $this->line(" - [{$detail['type']}] {$detail['email']} : {$detail['status']}");
            }
        }

        if ($this->option('scan-domains')) {
            $this->info('Vérification préventive des domaines DNS MX des utilisateurs...');
            $scan = $service->scanUsersForInvalidDomains(300);
            $this->info("Domaines vérifiés : {$scan['checked']}, domaines invalides exclus : {$scan['excluded']}");
            foreach ($scan['emails'] as $badEmail) {
                $this->warn(" - Domaine sans MX exclu : {$badEmail}");
            }
        }

        $this->info('Surveillance des rebonds terminée.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ArchiveUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-unverified {--days=7 : Nombre de jours sans vérification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive les comptes utilisateurs non vérifiés créés depuis plus de X jours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $count = User::whereNull('email_verified_at')
            ->where('is_archived', false)
            ->where('created_at', '<=', $cutoffDate)
            ->update([
                'is_archived' => true,
            ]);

        $this->info("Archivage réussi : {$count} compte(s) non vérifié(s) créé(s) il y a plus de {$days} jours ont été archivés.");

        return Command::SUCCESS;
    }
}

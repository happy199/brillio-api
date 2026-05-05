<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Console\Command;

class CleanupTypoEmails extends Command
{
    protected $signature = 'app:cleanup-typo-emails {--dry-run : Only show the counts without updating}';

    protected $description = 'Identify and block users with obvious typo domains in their emails';

    protected $typoDomains = [
        'icoud.com',
        'icloude.com',
        'gamail.com',
        'gamil.com',
        'gmai.com',
        'gmal.com',
        'yaho.com',
        'yhaoo.com',
        'outlok.com',
        'hotmal.com',
        'gmaill.com',
        'mailcom',
        'g-mail.com',
        'icloud.fr', // not necessarily a typo but often misidentified if someone meant .com
        'wanado.fr', // wanadoo.fr
        'lapost.net', // laposte.net
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        foreach ($this->typoDomains as $domain) {
            $this->processDomain($domain, $dryRun);
        }

        $this->info('Cleanup process completed.');
    }

    private function processDomain($domain, $dryRun)
    {
        $pattern = "%@{$domain}";

        $usersCount = User::where('email', 'like', $pattern)->count();
        $subscribersCount = NewsletterSubscriber::where('email', 'like', $pattern)->count();

        if ($usersCount > 0 || $subscribersCount > 0) {
            $this->warn("Found domain: {$domain}");
            $this->line("- Users: {$usersCount}");
            $this->line("- Subscribers: {$subscribersCount}");

            if (! $dryRun) {
                if ($usersCount > 0) {
                    // Pour les utilisateurs, on les bloque pour qu'ils ne reçoivent plus de mails automatiques
                    User::where('email', 'like', $pattern)->update([
                        'is_blocked' => true,
                        'archived_at' => now(),
                    ]);
                    $this->info('  -> Users blocked and archived.');
                }

                if ($subscribersCount > 0) {
                    // Pour les abonnés à la newsletter, on les désabonne
                    NewsletterSubscriber::where('email', 'like', $pattern)->update([
                        'status' => 'unsubscribed',
                    ]);
                    $this->info('  -> Subscribers unsubscribed.');
                }
            }
        }
    }
}

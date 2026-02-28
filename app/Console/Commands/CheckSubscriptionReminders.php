<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\MentorshipNotificationService;
use Illuminate\Console\Command;

class CheckSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscriptions-check-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check organizations for upcoming subscription expirations and send reminders';

    protected $notificationService;

    public function __construct(MentorshipNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking subscription expirations...');

        // 1. Check for Downgrades (Expired since yesterday)
        $expiredOrgs = Organization::where('subscription_plan', '!=', Organization::PLAN_FREE)
            ->where('subscription_expires_at', '<', now())
            ->get();

        foreach ($expiredOrgs as $org) {
            $this->info("Downgrading Organization: {$org->name}");
            $org->update([
                'subscription_plan' => Organization::PLAN_FREE,
                'subscription_expires_at' => null,
                'auto_renew' => false,
            ]);

            $this->notificationService->sendSubscriptionDowngradedNotification($org);
        }

        // 2. Reminders intervals
        $intervals = [
            ['diff' => 30 * 24 * 60, 'label' => '1 mois', 'tolerance' => 60], // 30 days
            ['diff' => 7 * 24 * 60, 'label' => '1 semaine', 'tolerance' => 60], // 7 days
            ['diff' => 1 * 24 * 60, 'label' => '1 jour', 'tolerance' => 60], // 1 day
            ['diff' => 1 * 60, 'label' => '1 heure', 'tolerance' => 10], // 1 hour
        ];

        $runningOrgs = Organization::where('subscription_plan', '!=', Organization::PLAN_FREE)
            ->where('subscription_expires_at', '>', now())
            ->get();

        foreach ($runningOrgs as $org) {
            $expiresAt = $org->subscription_expires_at;
            $minutesToExpiry = now()->diffInMinutes($expiresAt, false);

            foreach ($intervals as $interval) {
                // Check if we are within the notification window
                if ($minutesToExpiry > ($interval['diff'] - $interval['tolerance']) && $minutesToExpiry <= $interval['diff']) {
                    // Logic to avoid double sending: could use a cache or a 'last_reminder_sent' metadata field
                    $cacheKey = "sub_reminder_{$org->id}_{$interval['label']}_{$expiresAt->timestamp}";

                    if (! cache()->has($cacheKey)) {
                        $this->info("Sending {$interval['label']} reminder to: {$org->name}");
                        $this->notificationService->sendSubscriptionExpiringNotification($org, $interval['label']);
                        cache()->put($cacheKey, true, now()->addDays(2));
                    }
                }
            }
        }

        $this->info('Subscription check completed.');
    }
}

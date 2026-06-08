<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DowngradeExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizations:downgrade-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downgrade organizations with expired subscriptions to the free plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        $expiredOrganizations = Organization::where('subscription_plan', '!=', Organization::PLAN_FREE)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now())
            ->get();

        if ($expiredOrganizations->isEmpty()) {
            $this->info('No expired subscriptions found.');

            return 0;
        }

        foreach ($expiredOrganizations as $organization) {
            $this->warn("Downgrading organization: {$organization->name} (ID: {$organization->id})");

            $oldPlan = $organization->subscription_plan;
            $newPlan = $organization->pending_downgrade_to ?: Organization::PLAN_FREE;

            $organization->update([
                'subscription_plan' => $newPlan,
                'subscription_expires_at' => null,
                'auto_renew' => false,
                'pending_downgrade_to' => null,
            ]);

            try {
                app(\App\Services\MentorshipNotificationService::class)->sendSubscriptionDowngradedNotification($organization, $newPlan);
            } catch (\Exception $e) {
                Log::error("Failed to send subscription downgrade notification for organization {$organization->id}: ".$e->getMessage());
            }

            Log::info('Organization subscription downgraded automatically', [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'previous_plan' => $oldPlan,
                'new_plan' => $newPlan,
                'downgraded_at' => now()->toDateTimeString(),
            ]);
        }

        $this->info('Downgrade process completed.');

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsletterJob;
use App\Models\EmailCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringCampaigns extends Command
{
    protected $signature = 'campaigns:process-recurring';

    protected $description = 'Process recurring email campaigns and generate individual sends';

    public function handle()
    {
        $campaigns = EmailCampaign::where('is_recurring', true)
            ->where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No recurring campaigns to process.');

            return;
        }

        foreach ($campaigns as $master) {
            $this->info("Processing recurring campaign: {$master->subject}");

            // 1. Re-evaluate recipients based on filters
            $emails = $this->getRecipientsFromFilters($master->recipient_filters);

            if (empty($emails)) {
                $this->warn("No recipients found for campaign {$master->id}. Skipping this run.");
                $this->updateNextRun($master);

                continue;
            }

            // 2. Create child campaign (instance)
            $instance = EmailCampaign::create([
                'parent_id' => $master->id,
                'subject' => $master->subject,
                'body' => $master->body,
                'type' => $master->type,
                'is_recurring' => false,
                'recipients_count' => count($emails),
                'recipient_emails' => $emails,
                'status' => 'queued',
                'sent_by' => $master->sent_by ?? 1,
                'attachments' => $master->attachments,
            ]);

            // 3. Dispatch Job
            SendNewsletterJob::dispatch($instance);

            // 4. Update master
            $this->updateNextRun($master);
        }
    }

    private function getRecipientsFromFilters($filters)
    {
        if (empty($filters)) {
            return [];
        }

        $type = $filters['type'] ?? 'all';
        $recipientEmails = [];

        switch ($type) {
            case 'all':
                $recipientEmails = NewsletterSubscriber::active()->pluck('email')->toArray();
                break;
            case 'all_users':
                $recipientEmails = User::whereNull('archived_at')->pluck('email')->toArray();
                break;
            case 'custom':
                if (! empty($filters['custom_emails'])) {
                    $emails = preg_split('/[,\n\r;]+/', $filters['custom_emails']);
                    $recipientEmails = array_filter(array_map('trim', $emails), function ($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL);
                    });
                }
                break;
            case 'specific_population':
                if (! empty($filters['populations'])) {
                    $recipientEmails = User::whereIn('user_type', $filters['populations'])
                        ->whereNull('archived_at')
                        ->pluck('email')
                        ->toArray();
                }
                break;
        }

        return array_values(array_unique($recipientEmails));
    }

    private function updateNextRun($master)
    {
        $next = Carbon::parse($master->next_run_at);

        switch ($master->frequency) {
            case 'daily':
                $next->addDay();
                break;
            case 'weekly':
                $next->addWeek();
                break;
            case 'monthly':
                $next->addMonth();
                break;
            default:
                $next->addWeek();
                break;
        }

        $master->update([
            'last_run_at' => now(),
            'next_run_at' => $next,
            'status' => $next->toDateString() > $master->end_date ? 'completed' : 'active',
        ]);
    }
}

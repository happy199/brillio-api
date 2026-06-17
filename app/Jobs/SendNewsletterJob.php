<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recipients = $this->campaign->recipient_emails;
        $totalEmails = is_array($recipients) ? count($recipients) : 0;

        if ($totalEmails === 0) {
            $this->campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
                'recipients_count' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
            ]);

            return;
        }

        // Dynamic delay interval to spread the sends across the day
        // Spreads large campaigns over ~12 hours (43200 seconds)
        // Interval is capped between 2 and 15 seconds per email
        $interval = (int) (43200 / $totalEmails);
        if ($interval < 2) {
            $interval = 2;
        } elseif ($interval > 15) {
            $interval = 15;
        }

        // Mise à jour du statut et réinitialisation des compteurs
        $this->campaign->update([
            'status' => 'sending',
            'recipients_count' => $totalEmails,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        foreach ($recipients as $index => $email) {
            SendCampaignEmailJob::dispatch($this->campaign, $email)
                ->delay(now()->addSeconds($index * $interval));
        }
    }
}

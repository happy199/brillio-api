<?php

namespace App\Jobs;

use App\Mail\CampaignNewsletterMail;
use App\Models\EmailCampaign;
use App\Services\EmailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailCampaign $campaign, string $email)
    {
        $this->campaign = $campaign;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmailDeliveryService $deliveryService)
    {
        // Safety check: if the campaign was deleted
        if (! $this->campaign || ! $this->campaign->exists) {
            return;
        }

        if ($deliveryService->isExcludedEmail($this->email)) {
            Log::info('E-mail de campagne ignoré (adresse exclue)', ['email' => $this->email]);
            $this->incrementProcessedCount();

            return;
        }

        try {
            Mail::to($this->email)->send(new CampaignNewsletterMail($this->campaign));

            // Increment sent_count atomically
            $this->campaign->increment('sent_count');
        } catch (\Exception $e) {
            Log::error('Newsletter email failed in Job: '.$e->getMessage(), ['email' => $this->email, 'campaign_id' => $this->campaign->id]);

            $deliveryService->handleDeliveryFailure($this->email, $e);

            // Increment failed_count atomically
            $this->campaign->increment('failed_count');
        }

        $this->markCampaignCompleteIfDone();
    }

    private function incrementProcessedCount(): void
    {
        $this->campaign->increment('sent_count');
        $this->markCampaignCompleteIfDone();
    }

    private function markCampaignCompleteIfDone(): void
    {
        // Check if all emails have been processed
        $this->campaign->refresh();
        $totalProcessed = $this->campaign->sent_count + $this->campaign->failed_count;
        if ($totalProcessed >= $this->campaign->recipients_count) {
            $this->campaign->update([
                'status' => $this->campaign->failed_count > 0 ? 'partial' : 'sent',
                'sent_at' => now(),
            ]);
        }
    }
}

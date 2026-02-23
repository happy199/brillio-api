<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $subject = $this->campaign->subject;
        $body = $this->campaign->body;

        $sent = 0;
        $failed = 0;

        // Mise à jour du statut
        $this->campaign->update(['status' => 'sending']);

        foreach ($recipients as $email) {
            try {
                Mail::send([], [], function ($message) use ($email, $subject, $body) {
                    $message->to($email)
                        ->subject($subject)
                        ->html($body);
                });
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Newsletter email failed in Job: '.$e->getMessage(), ['email' => $email]);
            }
        }

        $this->campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed > 0 ? 'partial' : 'sent',
            'sent_at' => now(),
        ]);
    }
}

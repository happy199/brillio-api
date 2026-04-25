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
                // On génère le HTML complet à partir du template Premium
                $fullHtml = view('emails.newsletter', [
                    'content' => $body,
                    'subject' => $subject,
                ])->render();

                Mail::send([], [], function ($message) use ($email, $subject, $fullHtml) {
                    $message->to($email)->subject($subject);

                    // Conversion des images Base64 en CID (Inline) dans le HTML final
                    $finalHtml = $fullHtml;
                    if (preg_match_all('/src="data:image\/(.*?);base64,(.*?)"/', $finalHtml, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $index => $match) {
                            $extension = $match[1];
                            $imageData = base64_decode($match[2]);
                            $filename = 'image_'.md5($match[2]).'.'.$extension;

                            $cid = $message->embedData($imageData, $filename, 'image/'.$extension);
                            $finalHtml = str_replace($match[0], 'src="'.$cid.'"', $finalHtml);
                        }
                    }

                    $message->html($finalHtml);

                    // Pièces jointes
                    if (! empty($this->campaign->attachments)) {
                        foreach ($this->campaign->attachments as $attachment) {
                            $message->attach(storage_path('app/public/'.$attachment['path']), [
                                'as' => $attachment['name'],
                                'mime' => $attachment['mime'],
                            ]);
                        }
                    }
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

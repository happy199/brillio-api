<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $body = $this->campaign->body;
        $isFullHtml = preg_match('/<!doctype|<html>/i', $body);

        if ($isFullHtml) {
            $finalHtml = $body;
        } else {
            $finalHtml = view('emails.newsletter', [
                'content' => $body,
                'subject' => $this->campaign->subject,
            ])->render();
        }

        // Conversion des images Base64 en CID (Inline) dans le HTML final
        $bodyToSend = $finalHtml;
        if (preg_match_all('/src="data:image\/(.*?);base64,(.*?)"/', $bodyToSend, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $extension = $match[1];
                $imageData = base64_decode($match[2]);
                $filename = 'image_'.hash('sha256', $match[2]).'.'.$extension;

                $cid = $this->embedData($imageData, $filename, 'image/'.$extension);
                $bodyToSend = str_replace($match[0], 'src="'.$cid.'"', $bodyToSend);
            }
        }

        $this->html($bodyToSend);

        // Pièces jointes
        if (! empty($this->campaign->attachments)) {
            foreach ($this->campaign->attachments as $attachment) {
                $this->attach(storage_path('app/public/'.$attachment['path']), [
                    'as' => $attachment['name'],
                    'mime' => $attachment['mime'],
                ]);
            }
        }

        return $this;
    }
}

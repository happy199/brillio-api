<?php

namespace App\Mail\Wallet;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutProcessed extends Mailable
{
    use Queueable, SerializesModels;

    public $payout;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(PayoutRequest $payout)
    {
        $this->payout = $payout;
        $this->user = $payout->mentorProfile->user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->payout->status === PayoutRequest::STATUS_COMPLETED ? 'validée' : 'échouée';
        return new Envelope(
            subject: "Votre demande de retrait a été {$status} - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.payout-processed',
        );
    }
}
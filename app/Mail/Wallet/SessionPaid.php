<?php

namespace App\Mail\Wallet;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SessionPaid extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $jeune;

    public $session;

    public $amount;

    /**
     * Create a new message instance.
     */
    public function __construct(User $jeune, MentoringSession $session, int $amount)
    {
        $this->jeune = $jeune;
        $this->session = $session;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de paiement de votre séance - Brillio',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.paid',
        );
    }
}

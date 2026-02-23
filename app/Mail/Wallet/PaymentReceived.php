<?php

namespace App\Mail\Wallet;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $mentor;

    public $session;

    public $amount;

    /**
     * Create a new message instance.
     */
    public function __construct(User $mentor, MentoringSession $session, int $amount)
    {
        $this->mentor = $mentor;
        $this->session = $session;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau paiement reçu pour votre séance - Brillio',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.received',
        );
    }
}

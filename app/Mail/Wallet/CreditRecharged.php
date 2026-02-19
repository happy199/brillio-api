<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreditRecharged extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $newBalance;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, int $amount, int $newBalance)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->newBalance = $newBalance;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmation de votre recharge de crédits - Brillio",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.recharged',
        );
    }
}
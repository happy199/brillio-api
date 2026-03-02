<?php

namespace App\Mail\Messages;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadMessagesReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $senderName,
        public int $messageCount,
        public string $conversationUrl,
        public string $recipientRole, // 'jeune' or 'mentor'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "💬 {$this->messageCount} message(s) en attente de {$this->senderName} - Brillio",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.messages.unread-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

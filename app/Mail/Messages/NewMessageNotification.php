<?php

namespace App\Mail\Messages;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $recipient;

    public $senderName;

    public $conversationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, string $senderName, string $conversationUrl)
    {
        $this->recipient = $recipient;
        $this->senderName = $senderName;
        $this->conversationUrl = $conversationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau message de '.$this->senderName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.messages.new-message',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

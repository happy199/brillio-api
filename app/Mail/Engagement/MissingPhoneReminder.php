<?php

namespace App\Mail\Engagement;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissingPhoneReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public $whatsappChannelUrl;

    public $whatsappGroupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->whatsappChannelUrl = 'https://whatsapp.com/channel/0029VbCwysMKmCPOHlfDBB0g';
        $this->whatsappGroupUrl = config('services.whatsapp.group_url', 'https://chat.whatsapp.com/LhQzbQlUG3QBeeSRlGlZnH');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔥 Ne rate plus rien : tes opportunités (Bourses, Emplois, Formations) sur WhatsApp !',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.engagement.missing-phone',
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

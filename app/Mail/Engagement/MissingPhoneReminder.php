<?php

namespace App\Mail\Engagement;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissingPhoneReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public $whatsappChannelUrl;

    public $whatsappGroupUrl;

    public $mentorWhatsappGroupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->whatsappChannelUrl = 'https://whatsapp.com/channel/0029VbCwysMKmCPOHlfDBB0g';
        $this->whatsappGroupUrl = config('services.whatsapp.group_url', 'https://chat.whatsapp.com/LhQzbQlUG3QBeeSRlGlZnH');
        $this->mentorWhatsappGroupUrl = config('services.whatsapp.mentor_group_url', 'https://chat.whatsapp.com/LDjPaMz5vy0AGllDILraKd?s=cl&p=i&mlu=4&ilr=4&iam=2');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->user->isMentor()) {
            return new Envelope(
                subject: '🤝 Rejoignez le réseau des mentors Brillio & activez vos alertes WhatsApp',
            );
        }

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
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

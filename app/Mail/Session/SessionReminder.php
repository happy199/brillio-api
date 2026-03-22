<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use App\Models\User;
use App\Traits\GeneratesCalendarLinks;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SessionReminder extends Mailable
{
    use GeneratesCalendarLinks, Queueable, SerializesModels;

    public MentoringSession $session;

    public User $recipient;

    public Collection $participants;

    public string $type; // '24h' or '1h'

    public string $calendarUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        MentoringSession $session,
        User $recipient,
        Collection $participants,
        string $type = '24h'
    ) {
        $this->session = $session;
        $this->recipient = $recipient;
        $this->participants = $participants;
        $this->type = $type;
        $this->calendarUrl = $this->generateGoogleCalendarUrl($session);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $time = $this->session->scheduled_at->format('H:i');
        $subject = $this->type === '1h'
            ? "⚡ Rappel : Votre session commence dans 1 heure ! ({$time})"
            : "⏰ Rappel : Session demain à {$time} - Brillio";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.reminder',
            with: [
                'session' => $this->session,
                'recipient' => $this->recipient,
                'participants' => $this->participants,
                'type' => $this->type,
                'calendarUrl' => $this->calendarUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->generateIcsContent($this->session, $this->recipient), 'invitation.ics')
                ->withMime('text/calendar'),
        ];
    }
}

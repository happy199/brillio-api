<?php

namespace App\Mail\Session;

use App\Models\MentoringSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $session;

    public $mentor;

    /**
     * Create a new message instance.
     */
    public function __construct(MentoringSession $session)
    {
        $this->session = $session;
        $this->mentor = $session->mentor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏳ Rappel : Votre compte rendu est attendu pour libérer vos revenus',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.session.report-reminder',
        );
    }
}

<?php

namespace App\Mail\Resource;

use App\Models\Resource;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResourceRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $resource;

    /**
     * Create a new message instance.
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre ressource a été dépubliée — Action requise',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resource.resource-rejected',
        );
    }
}

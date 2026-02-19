<?php

namespace App\Mail\Resource;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResourcePurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $resource;
    public $buyer;
    public $creditsEarned;

    /**
     * Create a new message instance.
     */
    public function __construct(Resource $resource, User $buyer, int $creditsEarned)
    {
        $this->resource = $resource;
        $this->buyer = $buyer;
        $this->creditsEarned = $creditsEarned;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "💰 Nouvelle vente ! Votre ressource a été achetée",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resource.resource-purchased',
        );
    }
}
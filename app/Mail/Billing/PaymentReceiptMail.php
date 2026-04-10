<?php

namespace App\Mail\Billing;

use App\Models\MonerooTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $transaction;

    public $entity;

    /**
     * Create a new message instance.
     */
    public function __construct(MonerooTransaction $transaction, $entity)
    {
        $this->transaction = $transaction;
        $this->entity = $entity;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre reçu de paiement et facture - Brillio Africa',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.receipt',
            with: [
                'transaction' => $this->transaction,
                'entity' => $this->entity,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdfs.invoice', [
            'transaction' => $this->transaction,
            'entity' => $this->entity,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Facture_'.$this->transaction->moneroo_transaction_id.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

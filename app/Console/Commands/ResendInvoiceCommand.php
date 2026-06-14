<?php

namespace App\Console\Commands;

use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendInvoiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:resend {transaction_id : L\'ID de la transaction (ID local ou ID de transaction Moneroo)} {--to= : Destinataire alternatif pour l\'email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renvoyer la facture officielle par email pour une transaction spécifique';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $transactionId = $this->argument('transaction_id');

        $transaction = MonerooTransaction::where('id', $transactionId)
            ->orWhere('moneroo_transaction_id', $transactionId)
            ->first();

        if (! $transaction) {
            $this->error("Transaction {$transactionId} introuvable.");

            return 1;
        }

        $user = $transaction->user;
        if (! $user) {
            $this->error("Utilisateur introuvable pour la transaction {$transaction->id}.");

            return 1;
        }

        $isOrgTransaction = ($transaction->metadata['user_type'] ?? '') === 'organization';
        $entity = $user;

        if ($isOrgTransaction && $user->organization) {
            $entity = $user->organization;
        }

        $recipientEmail = $this->option('to');
        if (empty($recipientEmail)) {
            $recipientEmail = ($entity instanceof \App\Models\Organization)
                ? ($entity->contact_email ?: $user->email)
                : $entity->email;
        }

        if (empty($recipientEmail)) {
            $this->error("Impossible de déterminer l'adresse email destinataire.");

            return 1;
        }

        $this->info("Envoi de la facture pour la transaction {$transaction->moneroo_transaction_id} à {$recipientEmail}...");

        try {
            Mail::to($recipientEmail)->sendNow(new PaymentReceiptMail($transaction, $entity));
            $this->info('Facture envoyée avec succès !');

            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi : ".$e->getMessage());

            return 1;
        }
    }
}

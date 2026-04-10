<?php

namespace App\Console\Commands;

use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestInvoiceGeneration extends Command
{
    protected $signature = 'test:invoice {search} {--to=} {--type=}';

    protected $description = 'Test generating an invoice for a user or org';

    public function handle()
    {
        $search = $this->argument('search');
        $receiverEmail = $this->option('to') ?? 'tidjani.happy@gmail.com';
        $type = $this->option('type') ?? 'user';

        if ($type === 'org') {
            $entity = Organization::where('name', 'like', "%{$search}%")->first();
            if (! $entity) {
                $this->error("Organization {$search} not found.");

                return;
            }
            // For transaction, we still need a user technically, let's find the first user attached
            $user = $entity->users()->first();
            if (! $user) {
                // fallback to first user in db
                $user = User::first();
            }
        } else {
            $user = User::where('email', $search)->first();
            if (! $user) {
                $this->error("User {$search} not found");

                return;
            }
            $entity = $user;
        }

        $amount = $type === 'org' ? 60000 : 15000;
        $description = $type === 'org'
            ? 'Abonnement Pro - 3 mois'
            : 'Achat Crédits: Pack Standard (150 crédits)';

        $transaction = MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'moneroo_transaction_id' => 'test_'.time(),
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => $type === 'org' ? 0 : 150,
            'completed_at' => now(),
            'metadata' => [
                'description' => $description,
                'payment_method' => 'Mobile Money',
                'user_type' => $type === 'org' ? 'organization' : $user->user_type,
            ],
        ]);

        $this->info("Génération du PDF et envoi du mail en cours pour l'entité: {$entity->name}...");

        try {
            // Force sending to the specified email instead of entity email
            Mail::to($receiverEmail)->send(new PaymentReceiptMail($transaction, $entity));
            $this->info("Facture {$entity->name} envoyée avec succès à {$receiverEmail} !");

            Mail::to($receiverEmail)->send(new \App\Mail\Billing\AdminPaymentNotificationMail($transaction, $entity));
            $this->info("Notification admin {$entity->name} envoyée avec succès à {$receiverEmail} !");
        } catch (\Exception $e) {
            $this->error('Erreur : '.$e->getMessage());
        }
    }
}

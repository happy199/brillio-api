<?php

namespace App\Console\Commands;

use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestInvoiceGeneration extends Command
{
    protected $signature = 'test:invoice {user_id}';

    protected $description = 'Test generating an invoice for a user';

    public function handle()
    {
        $user = User::find($this->argument('user_id'));
        if (! $user) {
            $this->error('User not found');

            return;
        }

        $transaction = new MonerooTransaction([
            'moneroo_transaction_id' => 'test_123456',
            'amount' => 5000,
            'currency' => 'XOF',
            'credits_amount' => 50,
            'completed_at' => now(),
            'metadata' => [
                'description' => 'Achat Test',
                'payment_method' => 'Mobile Money',
            ],
        ]);

        $this->info('Génération du PDF et envoi du mail en cours...');

        try {
            Mail::to($user->email)->send(new PaymentReceiptMail($transaction, $user));
            $this->info("Facture envoyée avec succès à {$user->email} !");
        } catch (\Exception $e) {
            $this->error('Erreur : '.$e->getMessage());
        }
    }
}

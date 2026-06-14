<?php

namespace Tests\Feature\Console;

use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_invoice_sends_email_to_user()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $transaction = MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'moneroo_transaction_id' => 'mon_test_123',
            'amount' => 15000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 150,
            'completed_at' => now(),
            'metadata' => [
                'description' => 'Achat Crédits',
                'user_type' => 'jeune',
            ],
        ]);

        $this->artisan('invoice:resend', ['transaction_id' => $transaction->id])
            ->expectsOutput('Envoi de la facture pour la transaction mon_test_123 à user@example.com...')
            ->expectsOutput('Facture envoyée avec succès !')
            ->assertExitCode(0);

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) use ($user) {
            return $mail->hasTo('user@example.com') && $mail->entity->id === $user->id;
        });
    }

    public function test_resend_invoice_sends_email_to_organization()
    {
        Mail::fake();

        $org = Organization::factory()->create([
            'contact_email' => 'org@example.com',
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
            'email' => 'admin@example.com',
        ]);

        // Link organization to user
        $org->users()->attach($user, ['role' => 'admin']);

        $transaction = MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'moneroo_transaction_id' => 'mon_org_123',
            'amount' => 60000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 0,
            'completed_at' => now(),
            'metadata' => [
                'description' => 'Abonnement Pro',
                'user_type' => 'organization',
            ],
        ]);

        $this->artisan('invoice:resend', ['transaction_id' => $transaction->id])
            ->expectsOutput('Envoi de la facture pour la transaction mon_org_123 à org@example.com...')
            ->expectsOutput('Facture envoyée avec succès !')
            ->assertExitCode(0);

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) use ($org) {
            return $mail->hasTo('org@example.com') && $mail->entity->id === $org->id;
        });
    }

    public function test_resend_invoice_override_recipient()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        $transaction = MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'moneroo_transaction_id' => 'mon_test_123',
            'amount' => 15000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 150,
            'completed_at' => now(),
            'metadata' => ['user_type' => 'jeune'],
        ]);

        $this->artisan('invoice:resend', [
            'transaction_id' => $transaction->id,
            '--to' => 'override@example.com',
        ])
            ->expectsOutput('Envoi de la facture pour la transaction mon_test_123 à override@example.com...')
            ->expectsOutput('Facture envoyée avec succès !')
            ->assertExitCode(0);

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) {
            return $mail->hasTo('override@example.com');
        });
    }
}

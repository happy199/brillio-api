<?php

namespace Tests\Feature\Admin;

use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminResendInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guest_cannot_resend_invoice()
    {
        $user = User::factory()->create();
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

        $response = $this->post(route('admin.accounting.resend-invoice', $transaction->id));
        $response->assertRedirect('/rejoindre');
    }

    public function test_non_admin_cannot_resend_invoice()
    {
        $user = User::factory()->create(['is_admin' => false]);
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

        $response = $this->actingAs($user)->post(route('admin.accounting.resend-invoice', $transaction->id));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_resend_invoice_to_user()
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
            'metadata' => [
                'description' => 'Achat Crédits',
                'user_type' => 'jeune',
            ],
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.accounting.resend-invoice', $transaction->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) use ($user) {
            return $mail->hasTo('user@example.com') && $mail->entity->id === $user->id;
        });
    }

    public function test_admin_can_resend_invoice_to_organization()
    {
        Mail::fake();

        $org = Organization::factory()->create(['contact_email' => 'org@example.com']);
        $user = User::factory()->create(['organization_id' => $org->id]);
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

        $response = $this->actingAs($this->admin)->post(route('admin.accounting.resend-invoice', $transaction->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) use ($org) {
            return $mail->hasTo('org@example.com') && $mail->entity->id === $org->id;
        });
    }

    public function test_resend_invoice_fails_for_invalid_transaction()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.accounting.resend-invoice', 99999));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Transaction introuvable.');
    }
}

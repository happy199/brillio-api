<?php

namespace Tests\Feature\Admin;

use App\Models\MonerooTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccountingEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    private const ACHAT_CREDITS_50 = 'Achat Crédits 50';

    protected $admin;

    protected $user;

    protected $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create(['email' => 'client@example.com']);

        $this->transaction = MonerooTransaction::create([
            'user_id' => $this->user->id,
            'user_type' => get_class($this->user),
            'moneroo_transaction_id' => 'mon_test_999',
            'amount' => 25000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 250,
            'completed_at' => now(),
            'metadata' => [
                'description' => 'Achat Crédits 250',
                'user_type' => 'jeune',
            ],
        ]);
    }

    public function test_admin_can_view_accounting_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.index'));
        $response->assertStatus(200);
        $response->assertViewHas('revenue', 25000);
    }

    public function test_admin_can_export_pdf()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.export-pdf', [
            'period' => 'custom',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_export_excel_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.export-excel', [
            'period' => 'month',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ÉTAT FINANCIER', $response->streamedContent());
        $this->assertStringContainsString('MON-'.$this->transaction->id, $response->streamedContent());
    }

    public function test_admin_can_filter_transaction_history_by_date()
    {
        // Another transaction out of range (2 months ago)
        $oldTransaction = MonerooTransaction::create([
            'user_id' => $this->user->id,
            'user_type' => get_class($this->user),
            'moneroo_transaction_id' => 'mon_test_old',
            'amount' => 5000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 50,
            'completed_at' => now()->subMonths(2),
            'metadata' => [
                'description' => self::ACHAT_CREDITS_50,
                'user_type' => 'jeune',
            ],
        ]);

        // Filter range: current month (includes main transaction, excludes old)
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.history', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');

        $references = collect($transactions->items())->pluck('reference')->toArray();
        $this->assertContains('MON-'.$this->transaction->id, $references);
        $this->assertNotContains('MON-'.$oldTransaction->id, $references);
    }

    public function test_admin_can_filter_history_with_only_start_date()
    {
        $oldTransaction = MonerooTransaction::create([
            'user_id' => $this->user->id,
            'user_type' => get_class($this->user),
            'moneroo_transaction_id' => 'mon_test_old_start',
            'amount' => 5000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 50,
            'completed_at' => now()->subMonths(2),
            'metadata' => ['description' => self::ACHAT_CREDITS_50, 'user_type' => 'jeune'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.accounting.history', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        $references = collect($transactions->items())->pluck('reference')->toArray();
        $this->assertContains('MON-'.$this->transaction->id, $references);
        $this->assertNotContains('MON-'.$oldTransaction->id, $references);
    }

    public function test_admin_can_filter_history_with_only_end_date()
    {
        $futureTransaction = MonerooTransaction::create([
            'user_id' => $this->user->id,
            'user_type' => get_class($this->user),
            'moneroo_transaction_id' => 'mon_test_future',
            'amount' => 5000,
            'currency' => 'XOF',
            'status' => 'completed',
            'credits_amount' => 50,
            'completed_at' => now()->addMonths(2),
            'metadata' => ['description' => self::ACHAT_CREDITS_50, 'user_type' => 'jeune'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.accounting.history', [
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        $references = collect($transactions->items())->pluck('reference')->toArray();
        $this->assertContains('MON-'.$this->transaction->id, $references);
        $this->assertNotContains('MON-'.$futureTransaction->id, $references);
    }

    public function test_admin_can_download_invoices_zip()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.download-invoices-zip', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }

    public function test_admin_can_view_individual_invoice_pdf()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.view-invoice', $this->transaction->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_download_individual_invoice_pdf()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.accounting.download-invoice', $this->transaction->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=Facture_mon_test_999.pdf');
    }
}

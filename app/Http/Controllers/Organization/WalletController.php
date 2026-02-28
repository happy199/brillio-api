<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display the organization's wallet and available credit packs.
     */
    public function index()
    {
        $organization = $this->getCurrentOrganization();

        $creditPacks = CreditPack::credits()
            ->where('user_type', 'organization')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $recentTransactions = \App\Models\WalletTransaction::where('organization_id', $organization->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $creditPrice = app(\App\Services\WalletService::class)->getCreditPrice('organization');

        return view('organization.wallet.index', compact('creditPacks', 'recentTransactions', 'creditPrice', 'organization'));
    }

    /**
     * Handle credit pack purchase.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $pack = CreditPack::findOrFail($request->pack_id);

        $amount = $pack->price;
        $description = 'Achat Crédits: '.$pack->name;

        // PACK-{orgId}-{packId}-{timestamp}
        $organization = $this->getCurrentOrganization();
        $reference = 'PACK-'.$organization->id.'-'.$pack->id.'-'.time();

        $returnUrl = route('organization.payment.callback');

        $monerooService = app(\App\Services\MonerooService::class);
        $user = auth()->user();

        // Create pending transaction record
        $localTransaction = \App\Models\MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'pending',
            'credits_amount' => $pack->credits,
            'metadata' => [
                'reference' => $reference,
                'pack_id' => $pack->id,
                'user_type' => 'organization',
            ],
        ]);

        $customer = [
            'email' => $user->email,
            'first_name' => $monerooService->splitName($user->name)['first_name'],
            'last_name' => $monerooService->splitName($user->name)['last_name'],
            'phone' => $user->phone,
        ];

        $paymentData = $monerooService->initializePayment(
            $amount,
            $description,
            $customer,
            [
                'reference' => $reference,
                'transaction_id' => $localTransaction->id,
            ],
            $returnUrl
        );

        if (isset($paymentData['checkout_url'])) {
            // Save Moneroo transaction ID
            $localTransaction->update([
                'moneroo_transaction_id' => $paymentData['id'],
            ]);

            return redirect($paymentData['checkout_url']);
        }

        return redirect()->back()->with('error', 'Erreur lors de l\'initialisation du paiement.');
    }

    /**
     * Display the organization's expense history.
     */
    public function history(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $query = \App\Models\WalletTransaction::where('organization_id', $organization->id)
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(15)->withQueryString();

        $creditPrice = app(\App\Services\WalletService::class)->getCreditPrice('organization');

        return view('organization.wallet.history', compact('transactions', 'creditPrice', 'organization'));
    }

    /**
     * Export expenses to PDF.
     */
    public function exportPdf(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $query = \App\Models\WalletTransaction::where('organization_id', $organization->id)
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();
        $creditPrice = app(\App\Services\WalletService::class)->getCreditPrice('organization');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('organization.wallet.export-pdf', [
            'transactions' => $transactions,
            'creditPrice' => $creditPrice,
            'organization' => $organization,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ]);

        return $pdf->download('historique-transactions-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export expenses to CSV.
     */
    public function exportCsv(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $query = \App\Models\WalletTransaction::where('organization_id', $organization->id)
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();
        $creditPrice = app(\App\Services\WalletService::class)->getCreditPrice('organization');

        $filename = 'transactions-brillio-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions, $creditPrice) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Description', 'Crédits', 'Valeur (FCFA)']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->created_at->format('d/m/Y H:i'),
                    match (strtolower($t->type)) {
                        'purchase', 'recharge' => 'Achat',
                        'subscription' => 'Abonnement',
                        'expense' => 'Ressource',
                        'distribution' => 'Distribution',
                        'payment' => 'Paiement',
                        default => ucfirst($t->type)
                    },
                    $t->description,
                    $t->amount,
                    $t->amount * $creditPrice,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

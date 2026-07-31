<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Billing\PaymentReceiptMail;
use App\Models\MonerooTransaction;
use App\Models\Organization;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AccountingController extends Controller
{
    private const TX_NOT_FOUND = 'Transaction introuvable.';

    private const FCFA_SUFFIX = ' FCFA';

    private const ACHAT_CREDITS = 'Achat Crédits';

    private const CIBLAGE_PATTERN = '%Ciblage%';

    private const RETRAIT_MENTOR = 'Retrait Mentor';

    private const ABS_AMOUNT = 'ABS(amount)';

    private const USER_MODEL = 'App\Models\User';

    public function index(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getFilterDates($request);

        // 1. Recettes (Cash In) : Transactions Moneroo complétées (Achats de packs)
        $revenue = MonerooTransaction::where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        // 2. Dépenses (Cash Out) : Payouts complétés (Retraits Mentors)
        $payouts = PayoutRequest::where('status', PayoutRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        // 3. Solde Net
        $netIncome = $revenue - $payouts;

        // 4. Revenus Services (Crédits Consommés) : Ciblage avancé
        $targetingRevenueCredits = WalletTransaction::where('type', 'service_fee')
            ->where('description', 'like', '%Ciblage%')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('ABS(amount)'));

        $estimatedTargetingRevenueFcfa = $targetingRevenueCredits * 100;

        // 5. Revenus Organisations (Achats de packs + Subscriptions via Moneroo)
        $orgRevenue = MonerooTransaction::where('status', 'completed')
            ->where('user_type', 'App\Models\User')
            ->whereHas('user', function ($q) {
                $q->where('user_type', 'organization');
            })
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        // 6. Données pour le Graphique (Évolution journalière sur la période)
        $chartData = $this->getChartData($startDate, $endDate);

        // 7. Transactions Récentes (Fusionnées)
        $recentTransactions = $this->getRecentTransactions($startDate, $endDate);

        return view('admin.accounting.index', compact(
            'revenue',
            'payouts',
            'netIncome',
            'orgRevenue',
            'targetingRevenueCredits',
            'estimatedTargetingRevenueFcfa',
            'chartData',
            'recentTransactions',
            'startDate',
            'endDate',
            'period'
        ));
    }

    private function getFilterDates(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|string|in:month,today,week,year,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $validated['period'] ?? 'month';
        $customStart = $validated['start_date'] ?? null;
        $customEnd = $validated['end_date'] ?? null;

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($period === 'today') {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->endOfDay();
        } elseif ($period === 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($period === 'year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($period === 'custom') {
            if (! empty($customStart)) {
                $startDate = Carbon::parse($customStart)->startOfDay();
            } else {
                $startDate = Carbon::now()->startOfMonth();
            }
            if (! empty($customEnd)) {
                $endDate = Carbon::parse($customEnd)->endOfDay();
            } else {
                $endDate = Carbon::now()->endOfMonth();
            }
        }

        return [$startDate, $endDate, $period];
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
        ]);

        $startDate = ! empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $endDate = ! empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

        // Récupérer les transactions filtrées
        $revenueQuery = MonerooTransaction::with(['user', 'user.organization'])
            ->where('status', 'completed');

        if ($startDate) {
            $revenueQuery->where('completed_at', '>=', $startDate);
        }
        if ($endDate) {
            $revenueQuery->where('completed_at', '<=', $endDate);
        }

        $revenueTransactions = $revenueQuery->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'date' => $t->completed_at,
                    'type' => 'in', // Entrée
                    'label' => self::ACHAT_CREDITS,
                    'amount' => $t->amount,
                    'user' => $t->user,
                    'reference' => 'MON-'.$t->id,
                ];
            });

        $payoutQuery = PayoutRequest::with(['mentorProfile.user', 'mentorProfile.user.organization'])
            ->where('status', PayoutRequest::STATUS_COMPLETED);

        if ($startDate) {
            $payoutQuery->where('completed_at', '>=', $startDate);
        }
        if ($endDate) {
            $payoutQuery->where('completed_at', '<=', $endDate);
        }

        $payoutTransactions = $payoutQuery->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->completed_at,
                    'type' => 'out', // Sortie
                    'label' => self::RETRAIT_MENTOR,
                    'amount' => $p->amount,
                    'user' => $p->mentorProfile->user,
                    'reference' => 'PAY-'.$p->id,
                ];
            });

        // Fusionner et trier
        $allTransactions = $revenueTransactions->concat($payoutTransactions)->sortByDesc('date');

        // Pagination manuelle
        $perPage = 20;
        $page = $validated['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $paginatedItems = $allTransactions->slice($offset, $perPage)->values();

        $transactions = new LengthAwarePaginator(
            $paginatedItems,
            $allTransactions->count(),
            $perPage,
            $page,
            ['path' => route('admin.accounting.history'), 'query' => $validated]
        );

        return view('admin.accounting.history', compact('transactions', 'startDate', 'endDate'));
    }

    public function resendInvoice($id)
    {
        $transaction = MonerooTransaction::with(['user', 'user.organization'])->find($id);

        if (! $transaction) {
            return redirect()->back()->with('error', self::TX_NOT_FOUND);
        }

        $user = $transaction->user;
        if (! $user) {
            return redirect()->back()->with('error', 'Utilisateur introuvable pour cette transaction.');
        }

        $isOrgTransaction = ($transaction->metadata['user_type'] ?? '') === 'organization';
        $entity = $user;

        if ($isOrgTransaction && $user->organization) {
            $entity = $user->organization;
        }

        $recipientEmail = ($entity instanceof Organization)
            ? ($entity->contact_email ?: $user->email)
            : $entity->email;

        if (empty($recipientEmail)) {
            return redirect()->back()->with('error', 'Impossible de déterminer l\'adresse email du destinataire.');
        }

        try {
            Mail::to($recipientEmail)->sendNow(new PaymentReceiptMail($transaction, $entity));

            return redirect()->back()->with('success', "La facture a bien été renvoyée à l'adresse {$recipientEmail}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Une erreur est survenue lors de l'envoi de la facture : ".$e->getMessage());
        }
    }

    private function getChartData($startDate, $endDate)
    {
        // Grouper par jour
        $revenueByDay = MonerooTransaction::where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $payoutsByDay = PayoutRequest::where('status', PayoutRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $dates = [];
        $revenueSeries = [];
        $payoutsSeries = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dates[] = $current->format('d/m');
            $revenueSeries[] = $revenueByDay[$dateStr] ?? 0;
            $payoutsSeries[] = $payoutsByDay[$dateStr] ?? 0;
            $current->addDay();
        }

        return [
            'labels' => $dates,
            'revenue' => $revenueSeries,
            'payouts' => $payoutsSeries,
        ];
    }

    private function getRecentTransactions($startDate, $endDate)
    {
        // On récupère les 20 dernières opérations (Mix Moneroo et Payouts)
        $latestRevenue = MonerooTransaction::with(['user', 'user.organization'])->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($t) {
                return [
                    'date' => $t->completed_at,
                    'type' => 'in', // Entrée
                    'label' => 'Achat Crédits',
                    'amount' => $t->amount,
                    'user' => $t->user,
                    'reference' => 'MON-'.$t->id,
                ];
            });

        $latestPayouts = PayoutRequest::with(['mentorProfile.user', 'mentorProfile.user.organization'])->where('status', PayoutRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->completed_at,
                    'type' => 'out', // Sortie
                    'label' => 'Retrait Mentor',
                    'amount' => $p->amount,
                    'user' => $p->mentorProfile->user,
                    'reference' => 'PAY-'.$p->id,
                ];
            });

        $merged = $latestRevenue->concat($latestPayouts)
            ->sortByDesc('date')
            ->take(20);

        return $merged;
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate, $period] = $this->getFilterDates($request);

        $data = $this->getFinancialData($startDate, $endDate);
        $chartData = $this->getChartData($startDate, $endDate);

        $pdf = Pdf::loadView('pdfs.financial-statement', array_merge($data, [
            'chartData' => $chartData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'period' => $period,
        ]));

        return $pdf->download('Etat_Financier_'.$startDate->format('d-m-Y').'_au_'.$endDate->format('d-m-Y').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $data = $this->getFinancialData($startDate, $endDate);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Etat_Financier_'.$startDate->format('d-m-Y').'_au_'.$endDate->format('d-m-Y').'.csv"',
        ];

        $callback = function () use ($startDate, $endDate, $data) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Title block
            fputcsv($file, ['ÉTAT FINANCIER & TRÉSORERIE - BRILLIO AFRICA']);
            fputcsv($file, ['Période', $startDate->format('d/m/Y').' au '.$endDate->format('d/m/Y')]);
            fputcsv($file, []);

            // Summary metrics
            fputcsv($file, ['INDICATEURS CLÉS', 'VALEUR']);
            fputcsv($file, ['Recettes (Cash In)', number_format($data['revenue'], 0, '', '').self::FCFA_SUFFIX]);
            fputcsv($file, ['Dépenses (Cash Out)', number_format($data['payouts'], 0, '', '').self::FCFA_SUFFIX]);
            fputcsv($file, ['Solde Net (Cash Flow)', number_format($data['netIncome'], 0, '', '').self::FCFA_SUFFIX]);
            fputcsv($file, ['Revenus Services (Crédits)', number_format($data['targetingRevenueCredits'], 0, '', '').' Crédits']);
            fputcsv($file, ['Revenus Services (Est. FCFA)', number_format($data['estimatedTargetingRevenueFcfa'], 0, '', '').self::FCFA_SUFFIX]);
            fputcsv($file, ['Revenus Organisations', number_format($data['orgRevenue'], 0, '', '').self::FCFA_SUFFIX]);
            fputcsv($file, []);

            // Transaction details
            fputcsv($file, ['DÉTAILS DES OPÉRATIONS DE LA PÉRIODE']);
            fputcsv($file, ['Date', 'Référence', 'Type', 'Libellé', 'Utilisateur', 'Montant (FCFA)']);

            foreach ($data['transactions'] as $t) {
                fputcsv($file, [
                    $t['date']->format('d/m/Y H:i'),
                    $t['reference'],
                    $t['type'] === 'in' ? 'Recette' : 'Dépense',
                    $t['label'],
                    $this->formatUserLabel($t['user']),
                    ($t['type'] === 'in' ? '+' : '-').$t['amount'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFinancialData($startDate, $endDate)
    {
        $revenue = MonerooTransaction::where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        $payouts = PayoutRequest::where('status', PayoutRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        $netIncome = $revenue - $payouts;

        $targetingRevenueCredits = WalletTransaction::where('type', 'service_fee')
            ->where('description', 'like', self::CIBLAGE_PATTERN)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw(self::ABS_AMOUNT));

        $estimatedTargetingRevenueFcfa = $targetingRevenueCredits * 100;

        $orgRevenue = MonerooTransaction::where('status', 'completed')
            ->where('user_type', self::USER_MODEL)
            ->whereHas('user', function ($q) {
                $q->where('user_type', 'organization');
            })
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('amount');

        // All transactions in period
        $latestRevenue = MonerooTransaction::with(['user', 'user.organization'])->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($t) {
                return [
                    'date' => $t->completed_at,
                    'type' => 'in',
                    'label' => self::ACHAT_CREDITS,
                    'amount' => $t->amount,
                    'user' => $t->user,
                    'reference' => 'MON-'.$t->id,
                ];
            });

        $latestPayouts = PayoutRequest::with(['mentorProfile.user', 'mentorProfile.user.organization'])->where('status', PayoutRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->completed_at,
                    'type' => 'out',
                    'label' => self::RETRAIT_MENTOR,
                    'amount' => $p->amount,
                    'user' => $p->mentorProfile->user,
                    'reference' => 'PAY-'.$p->id,
                ];
            });

        $transactions = $latestRevenue->concat($latestPayouts)->sortByDesc('date');

        return compact(
            'revenue',
            'payouts',
            'netIncome',
            'targetingRevenueCredits',
            'estimatedTargetingRevenueFcfa',
            'orgRevenue',
            'transactions'
        );
    }

    private function formatUserLabel($user)
    {
        if (! $user) {
            return 'Utilisateur inconnu';
        }

        if (($user->user_type ?? '') === 'organization' && $user->organization) {
            $label = $user->organization->name;
        } else {
            $label = $user->name ?? 'Utilisateur inconnu';
        }

        return $label.' ('.($user->email ?? '').')';
    }

    public function downloadInvoicesZip(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = ! empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $endDate = ! empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

        $query = MonerooTransaction::with(['user', 'user.organization'])->where('status', 'completed');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        $transactions = $query->orderBy('completed_at', 'desc')->get();

        if ($transactions->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune facture trouvée pour la période sélectionnée.');
        }

        $zip = new \ZipArchive;
        $zipFileName = 'Factures_Brillio_'.($startDate ? $startDate->format('Ymd') : 'all').'_'.($endDate ? $endDate->format('Ymd') : 'all').'.zip';
        $zipFilePath = tempnam(sys_get_temp_dir(), 'zip');

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Impossible de créer le fichier ZIP.');
        }

        foreach ($transactions as $transaction) {
            $user = $transaction->user;
            if (! $user) {
                continue;
            }

            $isOrgTransaction = ($transaction->metadata['user_type'] ?? '') === 'organization';
            $entity = ($isOrgTransaction && $user->organization) ? $user->organization : $user;

            $pdf = Pdf::loadView('pdfs.invoice', [
                'transaction' => $transaction,
                'entity' => $entity,
            ]);

            $pdfContent = $pdf->output();
            $fileName = 'Facture_'.$transaction->moneroo_transaction_id.'.pdf';
            $zip->addFromString($fileName, $pdfContent);
        }

        $zip->close();

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function viewInvoice($id)
    {
        [$transaction, $entity] = $this->getTransactionAndEntity($id);

        if (! $transaction) {
            abort(404, self::TX_NOT_FOUND);
        }

        if (! $entity) {
            abort(404, 'Utilisateur ou organisation introuvable.');
        }

        $pdf = Pdf::loadView('pdfs.invoice', [
            'transaction' => $transaction,
            'entity' => $entity,
        ]);

        return $pdf->stream('Facture_'.$transaction->moneroo_transaction_id.'.pdf');
    }

    public function downloadInvoice($id)
    {
        [$transaction, $entity] = $this->getTransactionAndEntity($id);

        if (! $transaction) {
            abort(404, self::TX_NOT_FOUND);
        }

        if (! $entity) {
            abort(404, 'Utilisateur ou organisation introuvable.');
        }

        $pdf = Pdf::loadView('pdfs.invoice', [
            'transaction' => $transaction,
            'entity' => $entity,
        ]);

        return $pdf->download('Facture_'.$transaction->moneroo_transaction_id.'.pdf');
    }

    private function getTransactionAndEntity($id)
    {
        $transaction = MonerooTransaction::with(['user', 'user.organization'])->find($id);
        if (! $transaction) {
            return [null, null];
        }

        $user = $transaction->user;
        if (! $user) {
            return [$transaction, null];
        }

        $isOrgTransaction = ($transaction->metadata['user_type'] ?? '') === 'organization';
        $entity = $user;

        if ($isOrgTransaction && $user->organization) {
            $entity = $user->organization;
        }

        return [$transaction, $entity];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonerooTransaction;
use App\Models\PayoutRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        // Période par défaut : Ce mois-ci
        $period = $request->get('period', 'month');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($period === 'today') {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->endOfDay();
        }
        elseif ($period === 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        }
        elseif ($period === 'year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        }
        elseif ($period === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
        }

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

    public function history(Request $request)
    {
        // Récupérer TOUTES les transactions (sans limite de date par défaut, ou paginées)
        $revenueTransactions = MonerooTransaction::with(['user', 'user.organization'])->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($t) {
            return [
            'date' => $t->completed_at,
            'type' => 'in', // Entrée
            'label' => 'Achat Crédits',
            'amount' => $t->amount,
            'user' => $t->user,
            'reference' => 'MON-' . $t->id
            ];
        });

        $payoutTransactions = PayoutRequest::with(['mentorProfile.user', 'mentorProfile.user.organization'])->where('status', PayoutRequest::STATUS_COMPLETED)
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($p) {
            return [
            'date' => $p->completed_at,
            'type' => 'out', // Sortie
            'label' => 'Retrait Mentor',
            'amount' => $p->amount,
            'user' => $p->mentorProfile->user,
            'reference' => 'PAY-' . $p->id
            ];
        });

        // Fusionner et trier
        $allTransactions = $revenueTransactions->concat($payoutTransactions)->sortByDesc('date');

        // Pagination manuelle
        $perPage = 20;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginatedItems = $allTransactions->slice($offset, $perPage)->values();

        $transactions = new LengthAwarePaginator(
            $paginatedItems,
            $allTransactions->count(),
            $perPage,
            $page,
        ['path' => $request->url(), 'query' => $request->query()]
            );

        return view('admin.accounting.history', compact('transactions'));
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
            'payouts' => $payoutsSeries
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
            'reference' => 'MON-' . $t->id
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
            'reference' => 'PAY-' . $p->id
            ];
        });

        $merged = $latestRevenue->concat($latestPayouts)
            ->sortByDesc('date')
            ->take(20);

        return $merged;
    }
}
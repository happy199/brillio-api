<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * Afficher la liste des payouts
     */
    public function index(Request $request)
    {
        $query = PayoutRequest::with('mentorProfile.user');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mentorProfile.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payouts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    /**
     * Afficher les détails d'un retrait
     */
    public function show(PayoutRequest $payout)
    {
        $payout->load('mentorProfile.user');

        return view('admin.payouts.show', compact('payout'));
    }

    /**
     * Approuver manuellement un retrait
     */
    public function approve(PayoutRequest $payout)
    {
        if (! in_array($payout->status, [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_PROCESSING]) || ! $payout->is_manual) {
            return back()->with('error', 'Ce retrait ne peut pas être approuvé manuellement.');
        }

        $payout->update([
            'status' => PayoutRequest::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Incrémenter le total retiré du mentor
        $payout->mentorProfile->increment('total_withdrawn', $payout->amount);

        // Notification email
        app(\App\Services\MentorshipNotificationService::class)->sendPayoutCompleted($payout);

        return back()->with('success', 'Le retrait a été marqué comme complété avec succès.');
    }

    /**
     * Rejeter un retrait (avec remboursement)
     */
    public function reject(Request $request, PayoutRequest $payout)
    {
        if (in_array($payout->status, [PayoutRequest::STATUS_COMPLETED, PayoutRequest::STATUS_FAILED])) {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $payout->update([
            'status' => PayoutRequest::STATUS_FAILED,
            'error_message' => $request->reason,
        ]);

        // Rembourser le solde FCFA
        $payout->mentorProfile->increment('available_balance', $payout->amount);

        // Rembourser les crédits
        try {
            $walletService = app(\App\Services\WalletService::class);
            $creditPrice = $walletService->getCreditPrice('mentor');
            $creditsRefund = intval($payout->amount / $creditPrice);

            $walletService->addCredits(
                $payout->mentorProfile->user,
                $creditsRefund,
                'refund',
                'Remboursement retrait rejeté par l\'admin : '.$request->reason,
                $payout
            );
        } catch (\Exception $e) {
            \Log::error('Admin Payout Rejection: Failed to refund credits', ['error' => $e->getMessage()]);
        }

        // Notification email
        app(\App\Services\MentorshipNotificationService::class)->sendPayoutFailed($payout);

        return back()->with('success', 'Le retrait a été rejeté et le mentor a été remboursé.');
    }
}

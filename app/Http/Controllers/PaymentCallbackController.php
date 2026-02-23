<?php

namespace App\Http\Controllers;

use App\Models\MonerooTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Handle return from Moneroo checkout
     */
    public function handle(Request $request)
    {
        // Moneroo sends: ?paymentId=py_xxx&paymentStatus=success
        $monerooPaymentId = $request->query('paymentId');
        $status = $request->query('paymentStatus');

        Log::info('Payment callback received', [
            'moneroo_payment_id' => $monerooPaymentId,
            'status' => $status,
            'query' => $request->query(),
        ]);

        // Find the transaction by Moneroo payment ID
        $transaction = MonerooTransaction::where('moneroo_transaction_id', $monerooPaymentId)->first();

        if (! $transaction) {
            Log::warning('Transaction not found in callback', [
                'moneroo_payment_id' => $monerooPaymentId,
            ]);

            return redirect()->route('jeune.wallet.index')
                ->withErrors(['error' => 'Transaction introuvable.']);
        }

        // Determine redirect based on user's type attribute
        $user = $transaction->user;
        $redirectRoute = ($user && $user->user_type === 'mentor')
            ? 'mentor.wallet.index'
            : 'jeune.wallet.index';

        // Check payment status
        if ($status === 'success' || $status === 'completed') {
            // Payment successful - wait for webhook to confirm
            if ($transaction->status === 'completed') {
                return redirect()->route($redirectRoute)
                    ->with('success', "Paiement réussi ! +{$transaction->credits_amount} crédits ajoutés.");
            }

            // Payment initiated but not yet confirmed by webhook
            return redirect()->route($redirectRoute)
                ->with('info', 'Paiement en cours de traitement. Vos crédits seront ajoutés sous peu.');
        }

        if ($status === 'cancelled') {
            $transaction->update(['status' => 'cancelled']);

            return redirect()->route($redirectRoute)
                ->with('warning', 'Paiement annulé.');
        }

        if ($status === 'failed') {
            $transaction->markAsFailed();

            return redirect()->route($redirectRoute)
                ->withErrors(['error' => 'Le paiement a échoué. Veuillez réessayer.']);
        }

        // Unknown status
        return redirect()->route($redirectRoute)
            ->with('info', 'Paiement en attente de confirmation.');
    }
}

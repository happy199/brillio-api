<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayoutJob;
use App\Models\PayoutRequest;
use App\Services\MonerooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayoutController extends Controller
{
    protected MonerooService $monerooService;

    protected \App\Services\WalletService $walletService;

    public function __construct(MonerooService $monerooService, \App\Services\WalletService $walletService)
    {
        $this->monerooService = $monerooService;
        $this->walletService = $walletService;
    }

    /**
     * Obtenir le solde disponible du mentor
     */
    public function getBalance(Request $request)
    {
        $mentorProfile = $request->user()->mentorProfile;

        if (! $mentorProfile) {
            return response()->json([
                'message' => 'Profil mentor non trouvé',
            ], 404);
        }

        return response()->json([
            'available_balance' => (float) $mentorProfile->available_balance,
            'total_withdrawn' => (float) $mentorProfile->total_withdrawn,
            'currency' => 'FCFA',
        ]);
    }

    /**
     * Récupérer les méthodes de paiement disponibles
     */
    public function getPayoutMethods()
    {
        $methods = $this->monerooService->getPayoutMethods();

        return response()->json([
            'success' => ! empty($methods),
            'methods' => $methods,
        ]);
    }

    /**
     * Créer une demande de payout
     */
    public function requestPayout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:5000',
            'payment_method' => 'required|string',
            'phone_number' => 'required|string',
            'country_code' => 'required|string|size:2', // ex: "BJ"
            'dial_code' => 'required|string|max:10', // ex: "+229"
        ], [
            'amount.required' => 'Le montant est requis',
            'amount.numeric' => 'Le montant doit être un nombre',
            'amount.min' => 'Le montant minimum est 5 000 FCFA',
            'payment_method.required' => 'La méthode de paiement est requise',
            'phone_number.required' => 'Le numéro de téléphone est requis',
            'country_code.required' => 'Le code pays est requis',
            'country_code.size' => 'Le code pays doit faire 2 caractères',
            'dial_code.required' => 'L\'indicatif téléphonique est requis',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mentorProfile = $request->user()->mentorProfile;

        if (! $mentorProfile) {
            return response()->json([
                'message' => 'Profil mentor non trouvé',
            ], 404);
        }

        $amount = $request->input('amount');

        // Vérifier le solde disponible
        if ($mentorProfile->available_balance < $amount) {
            return response()->json([
                'message' => 'Solde insuffisant',
                'available_balance' => (float) $mentorProfile->available_balance,
                'requested_amount' => (float) $amount,
            ], 422);
        }

        // Calculer les frais
        $fee = $this->monerooService->calculateFee($amount);
        $netAmount = $amount - $fee;

        // Créer la demande de payout
        $payout = PayoutRequest::create([
            'mentor_profile_id' => $mentorProfile->id,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'payment_method' => $request->input('payment_method'),
            'phone_number' => $request->input('phone_number'),
            'country_code' => $request->input('country_code'),
            'dial_code' => $request->input('dial_code'),
            'status' => PayoutRequest::STATUS_PENDING,
        ]);

        // Déduire du solde disponible immédiatement (FCFA)
        $mentorProfile->decrement('available_balance', $amount);

        // Déduire les crédits correspondants (Conversion inverse)
        $creditPrice = $this->walletService->getCreditPrice('mentor');
        $creditsToDeduct = intval($amount / $creditPrice); // ex: 5000 / 100 = 50 crédits

        try {
            // On déduit les crédits avec le type 'payout'
            $this->walletService->deductCredits(
                $request->user(),
                $creditsToDeduct,
                'payout',
                'Retrait de '.number_format($amount, 0, ',', ' ').' FCFA',
                $payout
            );
        } catch (\Exception $e) {
            // En cas d'erreur (ex: incohérence solde crédits vs solde FCFA), on annule tout
            // On re-crédite le solde FCFA
            $mentorProfile->increment('available_balance', $amount);
            // On supprime la demande de payout
            $payout->delete();

            return response()->json([
                'message' => 'Erreur de cohérence: Solde de crédits insuffisant pour ce retrait.',
                'error' => $e->getMessage(),
            ], 422);
        }

        // Dispatcher le job pour traitement automatique
        ProcessPayoutJob::dispatch($payout);

        // Notification email
        app(\App\Services\MentorshipNotificationService::class)->sendPayoutRequested($payout);

        return response()->json([
            'message' => 'Demande de retrait créée avec succès',
            'payout' => [
                'id' => $payout->id,
                'amount' => (float) $payout->amount,
                'fee' => (float) $payout->fee,
                'net_amount' => (float) $payout->net_amount,
                'payment_method' => $payout->payment_method,
                'phone_number' => $payout->phone_number,
                'status' => $payout->status,
                'created_at' => $payout->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * Obtenir l'historique des demandes de payout
     */
    public function getPayoutRequests(Request $request)
    {
        $mentorProfile = $request->user()->mentorProfile;

        if (! $mentorProfile) {
            return response()->json([
                'message' => 'Profil mentor non trouvé',
            ], 404);
        }

        $payouts = PayoutRequest::where('mentor_profile_id', $mentorProfile->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => (float) $payout->amount,
                    'fee' => (float) $payout->fee,
                    'net_amount' => (float) $payout->net_amount,
                    'payment_method' => $payout->payment_method,
                    'phone_number' => $payout->phone_number,
                    'status' => $payout->status,
                    'error_message' => $payout->error_message,
                    'created_at' => $payout->created_at->toISOString(),
                    'completed_at' => $payout->completed_at?->toISOString(),
                ];
            });

        return response()->json([
            'payouts' => $payouts,
        ]);
    }
}

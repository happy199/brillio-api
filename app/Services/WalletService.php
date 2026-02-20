<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Ajoute des crédits à un utilisateur ou une organisation
     */
    public function addCredits($entity, int $amount, string $type, ?string $description = null, $related = null)
    {
        if ($amount <= 0) {
            throw new \Exception("Le montant doit être positif.");
        }

        return DB::transaction(function () use ($entity, $amount, $type, $description, $related) {
            $isUser = $entity instanceof User;
            $isOrg = $entity instanceof \App\Models\Organization;

            if (!$isUser && !$isOrg) {
                throw new \Exception("L'entité doit être un utilisateur ou une organisation.");
            }

            // Créer la transaction
            $transactionData = [
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
            ];

            if ($isUser) {
                $transactionData['user_id'] = $entity->id;
            }
            else {
                $transactionData['organization_id'] = $entity->id;
            }

            $transaction = new WalletTransaction($transactionData);

            if ($related) {
                $transaction->related()->associate($related);
            }

            $transaction->save();

            // Mettre à jour le solde
            $entity->increment('credits_balance', $amount);

            // Si c'est un mentor qui gagne des crédits via une vente (type 'income'),
            // on met aussi à jour son available_balance en FCFA pour les payouts
            if ($isUser && $type === 'income' && $entity->user_type === 'mentor' && $entity->mentorProfile) {
                // Convertir les crédits en FCFA (prix crédit mentor = 100 FCFA par défaut)
                $creditPriceFcfa = $this->getCreditPrice('mentor');
                $amountFcfa = $amount * $creditPriceFcfa;

                $entity->mentorProfile->increment('available_balance', $amountFcfa);
            }

            return $transaction;
        });
    }

    /**
     * Dédit des crédits à un utilisateur ou une organisation
     */
    public function deductCredits($entity, int $cost, string $type, ?string $description = null, $related = null)
    {
        if ($cost <= 0) {
            throw new \Exception("Le coût doit être positif.");
        }

        if ($entity->credits_balance < $cost) {
            throw new \Exception("Solde insuffisant.");
        }

        return DB::transaction(function () use ($entity, $cost, $type, $description, $related) {
            $isUser = $entity instanceof User;
            $isOrg = $entity instanceof \App\Models\Organization;

            if (!$isUser && !$isOrg) {
                throw new \Exception("L'entité doit être un utilisateur ou une organisation.");
            }

            // Mentor Spending Logic: Consuming earned credits reduces withdrawable balance
            if ($isUser && $entity->user_type === 'mentor' && $entity->mentorProfile) {
                $creditPriceFcfa = $this->getCreditPrice('mentor');
                $breakdown = $this->getCreditBreakdown($entity);

                // Expenses are deducted from PURCHASED credits first, then EARNED.
                $purchasedAvailable = $breakdown['purchased'];

                $deductFromPurchased = min($purchasedAvailable, $cost);
                $deductFromEarned = $cost - $deductFromPurchased;

                if ($deductFromEarned > 0) {
                    // Calculate FCFA equivalent to remove
                    $amoutFcfaToRemove = $deductFromEarned * $creditPriceFcfa;

                    // Reduce available balance (clamping to 0 just in case)
                    if ($entity->mentorProfile->available_balance >= $amoutFcfaToRemove) {
                        $entity->mentorProfile->decrement('available_balance', $amoutFcfaToRemove);
                    }
                    else {
                        // Edge case: Inconsistent state, reset to 0
                        $entity->mentorProfile->update(['available_balance' => 0]);
                    }
                }
            }

            // Create Transaction
            $transactionData = [
                'amount' => -$cost,
                'type' => $type,
                'description' => $description,
            ];

            if ($isUser) {
                $transactionData['user_id'] = $entity->id;
            }
            else {
                $transactionData['organization_id'] = $entity->id;
            }

            $transaction = new WalletTransaction($transactionData);

            if ($related) {
                $transaction->related()->associate($related);
            }

            $transaction->save();

            // Update Balance
            $entity->decrement('credits_balance', $cost);

            return $transaction;
        });
    }

    /**
     * Get breakdown of credits (Purchased vs Earned) for a Mentor.
     * Earned credits are derived from the available FCFA balance.
     */
    public function getCreditBreakdown(User $user): array
    {
        if ($user->user_type !== 'mentor' || !$user->mentorProfile) {
            return ['purchased' => $user->credits_balance, 'earned' => 0];
        }

        $creditPrice = $this->getCreditPrice('mentor');
        if ($creditPrice <= 0)
            $creditPrice = 100; // Safety

        // Calculate earned credits based on FCFA balance
        // We floor it because credits are integers
        $earnedCredits = floor($user->mentorProfile->available_balance / $creditPrice);

        // Ensure we don't exceed total balance (in case of sync issues)
        $earnedCredits = min($earnedCredits, $user->credits_balance);

        $purchasedCredits = $user->credits_balance - $earnedCredits;

        return [
            'purchased' => max(0, $purchasedCredits),
            'earned' => max(0, $earnedCredits)
        ];
    }

    /**
     * Récupère le prix d'un crédit en FCFA
     */
    public function getCreditPrice(string $userType = 'jeune'): int
    {
        if ($userType === 'mentor') {
            return SystemSetting::getValue('credit_price_mentor', 100);
        }

        if ($userType === 'organization') {
            return SystemSetting::getValue('credit_price_organization', 150); // Default organization price
        }

        return SystemSetting::getValue('credit_price_jeune', 50);
    }

    /**
     * Récupère le coût d'une fonctionnalité en crédits
     */
    public function getFeatureCost(string $featureKey, int $default = 0): int
    {
        // Exemple keys: feature_cost_advanced_targeting
        return SystemSetting::getValue('feature_cost_' . $featureKey, $default);
    }
    /**
     * Effectue le paiement différé au mentor après la séance (Une fois le compte rendu soumis)
     */
    public function payoutMentor(\App\Models\MentoringSession $session)
    {
        if (!$session->is_paid || $session->status !== 'completed') {
            return;
        }

        // Vérifier si déjà payé pour éviter les doublons
        $alreadyPaid = WalletTransaction::where('user_id', $session->mentor_id)
            ->where('related_type', \App\Models\MentoringSession::class)
            ->where('related_id', $session->id)
            ->where('type', 'income')
            ->exists();

        if ($alreadyPaid) {
            return;
        }

        return DB::transaction(function () use ($session) {
            $mentor = $session->mentor;

            // Calcul des montants
            $commissionPercent = SystemSetting::getValue('mentorship_commission_percent', 10);

            $jeuneCreditPrice = $this->getCreditPrice('jeune');
            $grossFcfa = $session->price; // Total payé par le jeune en FCFA (via crédits)

            $commissionFcfa = floor($grossFcfa * ($commissionPercent / 100));
            $netFcfa = $grossFcfa - $commissionFcfa;

            $mentorCreditPrice = $this->getCreditPrice('mentor');
            $mentorCredits = floor($netFcfa / $mentorCreditPrice);

            $this->addCredits(
                $mentor,
                $mentorCredits,
                'income',
                "Rémunération séance terminée : {$session->title} (-{$commissionPercent}% com.)",
                $session
            );

            // Notification au mentor que l'argent est libéré
            app(\App\Services\MentorshipNotificationService::class)->sendIncomeReleased($session, $mentor, $mentorCredits);
        });
    }

    /**
     * Rembourse un jeune suite à une annulation
     * @param float $ratio Facteur de remboursement (0.75 ou 1.0)
     */
    public function refundJeune(\App\Models\MentoringSession $session, User $user, float $ratio = 1.0)
    {
        if (!$session->is_paid) {
            return;
        }

        // On cherche la transaction de débit initiale pour cette séance
        $debitTransaction = WalletTransaction::where('user_id', $user->id)
            ->where('related_type', \App\Models\MentoringSession::class)
            ->where('related_id', $session->id)
            ->where('amount', '<', 0)
            ->first();

        if (!$debitTransaction) {
            return;
        }

        $paidAmount = abs($debitTransaction->amount);
        $refundAmount = floor($paidAmount * $ratio);

        if ($refundAmount <= 0) {
            return;
        }

        $description = $ratio < 1.0
            ? "Remboursement partiel (75%) annulation tardive : {$session->title}"
            : "Remboursement intégral annulation : {$session->title}";

        return $this->addCredits(
            $user,
            $refundAmount,
            'refund',
            $description,
            $session
        );
    }
}
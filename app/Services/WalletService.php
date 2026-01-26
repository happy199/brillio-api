<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Ajoute des crédits à un utilisateur
     */
    public function addCredits(User $user, int $amount, string $type, ?string $description = null, $related = null)
    {
        if ($amount <= 0) {
            throw new \Exception("Le montant doit être positif.");
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $related) {
            // Créer la transaction
            $transaction = new WalletTransaction([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
            ]);

            if ($related) {
                $transaction->related()->associate($related);
            }

            $transaction->save();

            // Mettre à jour le solde utilisateur
            $user->increment('credits_balance', $amount);

            return $transaction;
        });
    }

    /**
     * Dédit des crédits à un utilisateur
     */
    public function deductCredits(User $user, int $cost, string $type, ?string $description = null, $related = null)
    {
        if ($cost <= 0) {
            throw new \Exception("Le coût doit être positif.");
        }

        if ($user->credits_balance < $cost) {
            throw new \Exception("Solde insuffisant.");
        }

        return DB::transaction(function () use ($user, $cost, $type, $description, $related) {
            // Créer la transaction (montant négatif)
            $transaction = new WalletTransaction([
                'user_id' => $user->id,
                'amount' => -$cost,
                'type' => $type,
                'description' => $description,
            ]);

            if ($related) {
                $transaction->related()->associate($related);
            }

            $transaction->save();

            // Mettre à jour le solde
            $user->decrement('credits_balance', $cost);

            return $transaction;
        });
    }

    /**
     * Récupère le prix d'un crédit en FCFA
     */
    public function getCreditPrice(string $userType = 'jeune'): int
    {
        if ($userType === 'mentor') {
            return SystemSetting::getValue('credit_price_mentor', 100);
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
}

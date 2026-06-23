<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasCreditValidation
{
    /**
     * Check if user has sufficient credits for a feature
     */
    protected function hasSufficientCredits($user, string $feature, int $defaultCost): bool
    {
        $cost = $this->walletService->getFeatureCost($feature, $defaultCost);

        return $user->credits_balance >= $cost;
    }

    /**
     * Get cost for a feature
     */
    protected function getFeatureCost(string $feature, int $defaultCost): int
    {
        return $this->walletService->getFeatureCost($feature, $defaultCost);
    }

    /**
     * Return insufficient credits error response
     */
    protected function insufficientCreditsError(int $cost): JsonResponse
    {
        return $this->error("Solde insuffisant ({$cost} crédits requis).", 402);
    }
}

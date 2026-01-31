<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payout Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les retraits des mentors
    |
    */

    // Montant minimum de retrait en FCFA
    'min_withdrawal' => env('PAYOUT_MIN_WITHDRAWAL', 5000),

    // Taux de frais de retrait (en pourcentage)
    // Par défaut 2% (0.02)
    'fee_rate' => env('PAYOUT_FEE_RATE', 0.02),

    // Frais minimum en FCFA
    // Si le calcul donne moins que ce montant, on applique ce minimum
    'min_fee' => env('PAYOUT_MIN_FEE', 100),
];

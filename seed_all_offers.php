<?php

use App\Models\CreditPack;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Packs de Crédits Organisation
$packs = [
    ['name' => 'Pack Initiation', 'credits' => 50, 'price' => 5000, 'promo_percent' => 0, 'display_order' => 1],
    ['name' => 'Pack Découverte', 'credits' => 100, 'price' => 9500, 'promo_percent' => 5, 'display_order' => 2, 'is_popular' => true],
    ['name' => 'Pack Croissance', 'credits' => 250, 'price' => 22500, 'promo_percent' => 10, 'display_order' => 3],
    ['name' => 'Pack Expansion', 'credits' => 500, 'price' => 42500, 'promo_percent' => 15, 'display_order' => 4],
];

foreach ($packs as $data) {
    CreditPack::updateOrCreate(
        ['user_type' => 'organization', 'credits' => $data['credits'], 'type' => 'credits'],
        array_merge($data, [
            'type' => 'credits',
            'is_active' => true,
            'description' => "Pack de {$data['credits']} crédits pour organisation.",
        ])
    );
}

// 2. Offres d'Abonnement
$plans = [
    [
        'name' => 'Abonnement Pro Mensuel',
        'target_plan' => 'pro',
        'duration_days' => 30,
        'price' => 20000,
        'display_order' => 1,
        'is_popular' => true,
        'description' => 'Accès complet aux statistiques et calendrier.',
    ],
    [
        'name' => 'Abonnement Pro Annuel',
        'target_plan' => 'pro',
        'duration_days' => 365,
        'price' => 200000,
        'display_order' => 2,
        'description' => 'Accès complet avec 2 mois gratuits.',
    ],
    [
        'name' => 'Abonnement Entreprise',
        'target_plan' => 'enterprise',
        'duration_days' => 30,
        'price' => 50000,
        'display_order' => 3,
        'description' => 'Support prioritaire et export complet de données.',
    ],
];

foreach ($plans as $data) {
    CreditPack::updateOrCreate(
        ['user_type' => 'organization', 'target_plan' => $data['target_plan'], 'duration_days' => $data['duration_days'], 'type' => 'subscription'],
        array_merge($data, [
            'type' => 'subscription',
            'is_active' => true,
        ])
    );
}

echo "Packs et Plans créés avec succès.\n";

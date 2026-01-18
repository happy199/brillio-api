<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalityCompatibility extends Model
{
    use HasFactory;

    protected $fillable = ['type_a', 'type_b', 'description'];

    /**
     * Récupère tous les types compatibles avec un type donné.
     * La relation est bidirectionnelle dans la logique, mais stockée unidirectionnellement ou bidirectionnellement selon l'implémentation.
     * Ici on va chercher dans les deux colonnes.
     */
    public static function getCompatibleTypes(string $type): array
    {
        $matchesA = self::where('type_a', $type)->pluck('type_b')->toArray();
        $matchesB = self::where('type_b', $type)->pluck('type_a')->toArray();

        return array_unique(array_merge($matchesA, $matchesB));
    }
}

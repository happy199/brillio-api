<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle PersonalityTest - Test de personnalité MBTI (via OpenMBTI)
 *
 * Stocke les résultats du test de personnalité des utilisateurs
 */
class PersonalityTest extends Model
{
    use HasFactory;

    /**
     * Types de personnalité MBTI avec leurs labels français
     */
    public const PERSONALITY_TYPES = [
        'INTJ' => 'L\'Architecte',
        'INTP' => 'Le Logicien',
        'ENTJ' => 'Le Commandant',
        'ENTP' => 'L\'Innovateur',
        'INFJ' => 'L\'Avocat',
        'INFP' => 'Le Médiateur',
        'ENFJ' => 'Le Protagoniste',
        'ENFP' => 'Le Campaigner',
        'ISTJ' => 'Le Logisticien',
        'ISFJ' => 'Le Défenseur',
        'ESTJ' => 'Le Directeur',
        'ESFJ' => 'Le Consul',
        'ISTP' => 'Le Virtuose',
        'ISFP' => 'L\'Aventurier',
        'ESTP' => 'L\'Entrepreneur',
        'ESFP' => 'L\'Amuseur',
    ];

    protected $fillable = [
        'user_id',
        'test_type',
        'raw_responses',
        'personality_type',
        'personality_label',
        'personality_description',
        'traits_scores',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_responses' => 'array',
            'traits_scores' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si le test est complété
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Retourne le label du type de personnalité
     */
    public static function getLabelForType(string $type): string
    {
        return self::PERSONALITY_TYPES[$type] ?? 'Type inconnu';
    }
}

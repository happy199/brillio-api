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
        'recommended_careers',
        'completed_at',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'raw_responses' => 'array',
            'traits_scores' => 'array',
            'recommended_careers' => 'array',
            'completed_at' => 'datetime',
            'is_current' => 'boolean',
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
     * Scope pour obtenir uniquement le test actuel
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope pour obtenir l'historique (tests non actuels)
     */
    public function scopeHistory($query)
    {
        return $query->where('is_current', false)->orderBy('completed_at', 'desc');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle MentorProfile - Profil détaillé des mentors
 */
class MentorProfile extends Model
{
    use HasFactory;

    /**
     * Spécialisations disponibles
     */
    public const SPECIALIZATIONS = [
        'tech' => 'Technologie & IT',
        'finance' => 'Finance & Banque',
        'health' => 'Santé & Médecine',
        'education' => 'Éducation',
        'engineering' => 'Ingénierie',
        'business' => 'Business & Entrepreneuriat',
        'law' => 'Droit',
        'arts' => 'Arts & Créativité',
        'science' => 'Sciences',
        'agriculture' => 'Agriculture',
        'other' => 'Autre',
    ];

    /**
     * Mapping entre les spécialisations mentor et les secteurs MBTI
     * Un mentor peut correspondre à plusieurs secteurs MBTI
     */
    public const SPECIALIZATION_TO_MBTI_SECTORS = [
        'tech' => ['tech'],
        'finance' => ['finance'],
        'health' => ['health'],
        'education' => ['education'],
        'engineering' => ['engineering', 'environment'],
        'business' => ['finance', 'communication'],
        'law' => ['law'],
        'arts' => ['creative', 'communication'],
        'science' => ['tech', 'health', 'environment'],
        'agriculture' => ['environment'],
        'other' => ['social'],
    ];

    protected $fillable = [
        'user_id',
        'bio',
        'current_position',
        'current_company',
        'years_of_experience',
        'specialization',
        'linkedin_profile_data',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'linkedin_profile_data' => 'array',
            'is_published' => 'boolean',
            'years_of_experience' => 'integer',
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
     * Relation vers les étapes du parcours
     */
    public function roadmapSteps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('position');
    }

    /**
     * Scope pour filtrer les profils publiés
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope pour filtrer par spécialisation
     */
    public function scopeBySpecialization($query, string $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    /**
     * Vérifie si le profil est complet (prêt à être publié)
     */
    public function isComplete(): bool
    {
        return !empty($this->bio)
            && !empty($this->current_position)
            && !empty($this->specialization)
            && $this->roadmapSteps()->count() >= 1;
    }

    /**
     * Retourne le label de la spécialisation
     */
    public function getSpecializationLabelAttribute(): string
    {
        return self::SPECIALIZATIONS[$this->specialization] ?? 'Non définie';
    }

    /**
     * Retourne les secteurs MBTI correspondant à cette spécialisation
     */
    public function getMbtiSectorsAttribute(): array
    {
        return self::SPECIALIZATION_TO_MBTI_SECTORS[$this->specialization] ?? [];
    }

    /**
     * Scope pour filtrer par secteur MBTI
     */
    public function scopeByMbtiSector($query, string $sectorCode)
    {
        // Trouver toutes les spécialisations qui correspondent à ce secteur
        $matchingSpecializations = [];
        foreach (self::SPECIALIZATION_TO_MBTI_SECTORS as $spec => $sectors) {
            if (in_array($sectorCode, $sectors)) {
                $matchingSpecializations[] = $spec;
            }
        }

        if (empty($matchingSpecializations)) {
            return $query->whereRaw('1 = 0'); // Retourne rien
        }

        return $query->whereIn('specialization', $matchingSpecializations);
    }

    /**
     * Scope pour filtrer par type de personnalité MBTI
     * Trouve les mentors dont la spécialisation correspond aux secteurs du type
     */
    public function scopeByMbtiType($query, string $mbtiType)
    {
        // Utiliser MbtiCareersService pour trouver les secteurs du type
        $sectors = \App\Services\MbtiCareersService::getSectorsForType($mbtiType);
        $sectorCodes = array_keys($sectors);

        if (empty($sectorCodes)) {
            return $query;
        }

        // Trouver toutes les spécialisations qui correspondent à ces secteurs
        $matchingSpecializations = [];
        foreach (self::SPECIALIZATION_TO_MBTI_SECTORS as $spec => $specSectors) {
            foreach ($specSectors as $sector) {
                if (in_array($sector, $sectorCodes)) {
                    $matchingSpecializations[] = $spec;
                    break;
                }
            }
        }

        if (empty($matchingSpecializations)) {
            return $query;
        }

        return $query->whereIn('specialization', array_unique($matchingSpecializations));
    }

    /**
     * Récupère les mentors recommandés pour un type de personnalité
     */
    public static function getRecommendedForMbtiType(string $mbtiType, int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return self::published()
            ->byMbtiType($mbtiType)
            ->with('user')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les mentors par secteur MBTI
     */
    public static function getBySector(string $sectorCode, int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return self::published()
            ->byMbtiSector($sectorCode)
            ->with('user')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle RoadmapStep - Étapes du parcours d'un mentor
 */
class RoadmapStep extends Model
{
    use HasFactory;

    /**
     * Types d'étapes disponibles
     */
    public const TYPE_EDUCATION = 'education';

    public const TYPE_WORK = 'work';

    public const TYPE_CERTIFICATION = 'certification';

    public const TYPE_ACHIEVEMENT = 'achievement';

    public const STEP_TYPES = [
        self::TYPE_EDUCATION => 'Formation',
        self::TYPE_WORK => 'Expérience professionnelle',
        self::TYPE_CERTIFICATION => 'Certification',
        self::TYPE_ACHIEVEMENT => 'Accomplissement',
    ];

    protected $fillable = [
        'mentor_profile_id',
        'step_type',
        'title',
        'institution_company',
        'location',
        'start_date',
        'end_date',
        'description',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'position' => 'integer',
        ];
    }

    /**
     * Relation vers le profil mentor
     */
    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class);
    }

    /**
     * Retourne le label du type d'étape
     */
    public function getStepTypeLabelAttribute(): string
    {
        return self::STEP_TYPES[$this->step_type] ?? 'Autre';
    }

    /**
     * Vérifie si l'étape est en cours (pas de date de fin)
     */
    public function isOngoing(): bool
    {
        return $this->end_date === null;
    }

    /**
     * Retourne la durée en texte lisible
     */
    public function getDurationAttribute(): string
    {
        $start = $this->start_date?->format('M Y');
        $end = $this->end_date?->format('M Y') ?? 'Présent';

        return "{$start} - {$end}";
    }
}

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
}

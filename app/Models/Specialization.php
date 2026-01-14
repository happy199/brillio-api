<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by_admin',
        'mentor_count',
    ];

    protected $casts = [
        'created_by_admin' => 'boolean',
        'mentor_count' => 'integer',
    ];

    /**
     * Boot method pour générer automatiquement le slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($specialization) {
            if (empty($specialization->slug)) {
                $specialization->slug = Str::slug($specialization->name);
            }
        });

        static::updating(function ($specialization) {
            if ($specialization->isDirty('name')) {
                $specialization->slug = Str::slug($specialization->name);
            }
        });
    }

    /**
     * Relation vers les profils mentors
     */
    public function mentorProfiles()
    {
        return $this->hasMany(MentorProfile::class);
    }

    /**
     * Relation vers les types MBTI
     */
    public function mbtiTypes()
    {
        return $this->hasMany(SpecializationMbtiType::class);
    }

    /**
     * Scope pour les spécialisations actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les spécialisations en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les spécialisations archivées
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Vérifier si liée à un type MBTI
     */
    public function isLinkedToMbtiType(string $code): bool
    {
        return $this->mbtiTypes()->where('mbti_type_code', $code)->exists();
    }

    /**
     * Obtenir les codes MBTI liés
     */
    public function getMbtiCodesAttribute(): array
    {
        return $this->mbtiTypes()->pluck('mbti_type_code')->toArray();
    }

    /**
     * Mettre à jour le compteur de mentors
     */
    public function updateMentorCount()
    {
        $this->mentor_count = $this->mentorProfiles()->count();
        $this->save();
    }

    /**
     * Lier des types MBTI
     */
    public function syncMbtiTypes(array $codes)
    {
        // Supprimer les anciens
        $this->mbtiTypes()->delete();

        // Ajouter les nouveaux
        foreach ($codes as $code) {
            $this->mbtiTypes()->create(['mbti_type_code' => $code]);
        }
    }
}

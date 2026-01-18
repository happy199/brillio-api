<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User - Utilisateur principal de la plateforme Brillio
 *
 * Gère les deux types d'utilisateurs : jeunes et mentors
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Types d'utilisateurs disponibles
     */
    public const TYPE_JEUNE = 'jeune';
    public const TYPE_MENTOR = 'mentor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'date_of_birth',
        'country',
        'city',
        'profile_photo_path',
        'profile_photo_url',
        'linkedin_url',
        'is_admin',
        'auth_provider',
        'provider_id',
        'onboarding_completed',
        'onboarding_data',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_admin' => 'boolean',
            'onboarding_completed' => 'boolean',
            'onboarding_data' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Vérifie si l'utilisateur est un jeune
     */
    public function isJeune(): bool
    {
        return $this->user_type === self::TYPE_JEUNE;
    }

    /**
     * Vérifie si l'utilisateur est un mentor
     */
    public function isMentor(): bool
    {
        return $this->user_type === self::TYPE_MENTOR;
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Relation vers le test de personnalité actuel
     */
    public function personalityTest(): HasOne
    {
        return $this->hasOne(PersonalityTest::class)->where('is_current', true);
    }

    /**
     * Relation vers tous les tests de personnalité
     */
    public function personalityTests(): HasMany
    {
        return $this->hasMany(PersonalityTest::class)->orderByDesc('completed_at');
    }

    /**
     * Relation vers le profil mentor (si type mentor)
     */
    public function mentorProfile(): HasOne
    {
        return $this->hasOne(MentorProfile::class);
    }

    /**
     * Relation vers les conversations de chat
     */
    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    /**
     * Relation vers le profil jeune (si type jeune)
     */
    public function jeuneProfile(): HasOne
    {
        return $this->hasOne(JeuneProfile::class);
    }

    /**
     * Relation vers les documents académiques
     */
    public function academicDocuments(): HasMany
    {
        return $this->hasMany(AcademicDocument::class);
    }

    /**
     * Retourne l'URL complète de la photo de profil
     */
    public function getAvatarUrlAttribute(): ?string
    {
        // Priorite: photo OAuth > photo uploadee
        if ($this->attributes['profile_photo_url'] ?? null) {
            return $this->attributes['profile_photo_url'];
        }

        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return null;
    }

    /**
     * Verifie si l'onboarding est complete
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed === true;
    }
}

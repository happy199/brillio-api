<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'User model schema',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Jean Dupont'),
        new OA\Property(property: 'email', type: 'string', example: 'jean@example.com'),
        new OA\Property(property: 'user_type', type: 'string', example: 'jeune'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'country', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', nullable: true),
        new OA\Property(property: 'profile_photo_url', type: 'string', nullable: true),
        new OA\Property(property: 'organization_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'organization_role', type: 'string', nullable: true, example: 'admin'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Types d'utilisateurs disponibles
     */
    public const TYPE_JEUNE = 'jeune';

    public const TYPE_MENTOR = 'mentor';

    public const TYPE_ORGANIZATION = 'organization';

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
        'sponsored_by_organization_id',
        'organization_id',
        'organization_role',
        'referral_code_used',
        'is_archived',
        'archived_at',
        'archived_reason',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
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
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
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
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        if ($this->isOrganization()) {
            $this->notify(new \App\Notifications\VerifyOrganizationEmail);
        } else {
            $this->notify(new \App\Notifications\VerifyEmail);
        }
    }

    /**
     * Vérifie si l'utilisateur est une organisation
     */
    public function isOrganization(): bool
    {
        return $this->user_type === self::TYPE_ORGANIZATION;
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
     * Relation vers les ressources consultées
     */
    public function resourceViews(): HasMany
    {
        return $this->hasMany(ResourceView::class);
    }

    /**
     * Relation vers les achats effectués
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Relation vers les profils mentors consultés par ce jeune
     */
    public function mentorProfileViews(): HasMany
    {
        return $this->hasMany(MentorProfileView::class, 'user_id');
    }

    /**
     * Relation vers les documents académiques
     */
    public function academicDocuments(): HasMany
    {
        return $this->hasMany(AcademicDocument::class);
    }

    /**
     * Relation vers les ressources créées par l'utilisateur (mentor/admin)
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Relation vers les transactions du portefeuille
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // --- MENTORSHIP SYSTEM RELATIONS ---

    /**
     * Mentorats où l'utilisateur est le mentor
     */
    public function mentorshipsAsMentor(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'mentor_id');
    }

    /**
     * Mentorats où l'utilisateur est le jeune (mentoré)
     */
    public function mentorshipsAsMentee(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'mentee_id');
    }

    /**
     * Disponibilités du mentor
     */
    public function mentorAvailabilities(): HasMany
    {
        return $this->hasMany(MentorAvailability::class, 'mentor_id');
    }

    /**
     * Séances créées par le mentor
     */
    public function mentoringSessionsAsMentor(): HasMany
    {
        return $this->hasMany(MentoringSession::class, 'mentor_id');
    }

    /**
     * Séances auxquelles le jeune participe
     */
    public function mentoringSessionsAsMentee(): BelongsToMany
    {
        return $this->belongsToMany(MentoringSession::class, 'mentoring_session_user', 'user_id', 'mentoring_session_id')
            ->withPivot('status', 'rejection_reason')
            ->withTimestamps();
    }

    /**
     * Retourne l'URL complète de la photo de profil
     */
    public function getAvatarUrlAttribute(): string
    {
        // Priorite: photo locale (téléchargée/uploadée) > photo OAuth
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        if ($this->attributes['profile_photo_url'] ?? null) {
            return $this->attributes['profile_photo_url'];
        }

        // Fallback: Initiales
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Verifie si l'onboarding est complete
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed === true;
    }

    /**
     * Calcule le pourcentage de complétion du profil du jeune.
     * Basé sur 10 critères (10% chacun).
     */
    public function getProfileCompletionPercentageAttribute(): int
    {
        if (! $this->isJeune()) {
            return 0;
        }

        $criteria = [
            'name' => ! empty($this->name),
            'photo' => ! empty($this->profile_photo_path) || ! empty($this->profile_photo_url),
            'phone' => ! empty($this->phone),
            'dob' => ! empty($this->date_of_birth),
            'location' => ! empty($this->city) || ! empty($this->country),
            'linkedin' => ! empty($this->linkedin_url),
            'bio' => ! empty($this->jeuneProfile?->bio),
            'cv' => ! empty($this->jeuneProfile?->cv_path),
            'portfolio' => ! empty($this->jeuneProfile?->portfolio_url),
            'personality' => $this->personalityTest()->exists(),
        ];

        $completedCount = count(array_filter($criteria));

        return $completedCount * 10;
    }

    /**
     * Liste les champs manquants du profil.
     */
    public function getMissingProfileFieldsAttribute(): array
    {
        if (! $this->isJeune()) {
            return [];
        }

        $fields = [
            'name' => 'Nom complet',
            'photo' => 'Photo de profil',
            'phone' => 'Numéro de téléphone',
            'dob' => 'Date de naissance',
            'location' => 'Ville ou pays',
            'linkedin' => 'Lien LinkedIn',
            'bio' => 'Biographie / Présentation',
            'cv' => 'Curriculum Vitae',
            'portfolio' => 'Lien Portfolio',
            'personality' => 'Test de personnalité',
        ];

        $criteria = [
            'name' => ! empty($this->name),
            'photo' => ! empty($this->profile_photo_path) || ! empty($this->profile_photo_url),
            'phone' => ! empty($this->phone),
            'dob' => ! empty($this->date_of_birth),
            'location' => ! empty($this->city) || ! empty($this->country),
            'linkedin' => ! empty($this->linkedin_url),
            'bio' => ! empty($this->jeuneProfile?->bio),
            'cv' => ! empty($this->jeuneProfile?->cv_path),
            'portfolio' => ! empty($this->jeuneProfile?->portfolio_url),
            'personality' => $this->personalityTest()->exists(),
        ];

        $missing = [];
        foreach ($criteria as $field => $isCompleted) {
            if (! $isCompleted) {
                $missing[] = $fields[$field];
            }
        }

        return $missing;
    }

    /**
     * Relation vers l'organisation qui a parrainé ce jeune utilisateur
     */
    public function sponsoringOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'sponsored_by_organization_id');
    }

    /**
     * Scope pour filtrer les utilisateurs sponsorisés par une organisation
     */
    public function scopeSponsoredByOrganization($query, $organizationId)
    {
        return $query->where('sponsored_by_organization_id', $organizationId);
    }

    /**
     * Vérifie si l'utilisateur est sponsorisé par une organisation
     */
    public function isSponsoredByOrganization(): bool
    {
        return ! is_null($this->sponsored_by_organization_id);
    }

    /**
     * Relation vers l'organisation gérée par cet utilisateur (pour les admins d'orga).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relation vers toutes les organisations auxquelles appartient l'utilisateur
     */
    public function organizations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot(['role', 'referral_code_used'])
            ->withTimestamps();
    }
}

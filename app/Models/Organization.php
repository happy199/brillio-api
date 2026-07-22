<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Organization',
    title: 'Organization',
    description: 'Organization model schema',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Brillio Org'),
        new OA\Property(property: 'slug', type: 'string', example: 'brillio-org'),
        new OA\Property(property: 'logo_url', type: 'string', nullable: true),
        new OA\Property(property: 'primary_color', type: 'string', example: '#f43f5e'),
        new OA\Property(property: 'secondary_color', type: 'string', example: '#e11d48'),
        new OA\Property(property: 'accent_color', type: 'string', example: '#fb7185'),
        new OA\Property(property: 'subscription_plan', type: 'string', example: 'enterprise'),
        new OA\Property(property: 'credits_balance', type: 'integer', example: 1000),
    ]
)]
class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'contact_email',
        'phone',
        'website',
        'sector',
        'description',
        'status',
        'custom_domain',
        'credits_balance',
        'subscription_plan',
        'subscription_expires_at',
        'auto_renew',
        'pending_downgrade_to',
        'private_circle_enabled',
        'private_circle_plus_enabled',
        'disable_onboarding_steps',
    ];

    /**
     * Subscription Plans
     */
    public const PLAN_FREE = 'free';

    public const PLAN_PRO = 'pro';

    public const PLAN_ENTERPRISE = 'enterprise';

    public const PLAN_ESTABLISHMENT = 'establishment';

    /**
     * Get the attributes that should be cast.
     */
    protected $casts = [
        'status' => 'string',
        'subscription_expires_at' => 'datetime',
        'auto_renew' => 'boolean',
        'private_circle_enabled' => 'boolean',
        'private_circle_plus_enabled' => 'boolean',
        'disable_onboarding_steps' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Organization $organization) {
            if (empty($organization->slug)) {
                $slug = Str::slug($organization->name);
                $originalSlug = $slug;
                $counter = 1;

                // Check if slug exists and increment until we find a unique one
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug.'-'.$counter;
                    $counter++;
                }

                $organization->slug = $slug;
            }
        });
    }

    /**
     * Get all invitations for this organization.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /**
     * Get all wallet transactions for this organization.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get all users linked to this organization via the pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role', 'referral_code_used'])
            ->withTimestamps();
    }

    /**
     * Get all mentors linked to this organization via the pivot table.
     */
    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->where('users.user_type', 'mentor')
            ->where('users.is_guest', false)
            ->withPivot('referral_code_used')
            ->withTimestamps();
    }

    /**
     * Get all sponsored users (legacy 1-N relationship for original source tracking).
     */
    public function sponsoredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'sponsored_by_organization_id');
    }

    /**
     * Get all establishments for this organization.
     */
    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }

    public function establishmentClicks(): HasManyThrough
    {
        return $this->hasManyThrough(EstablishmentClick::class, Establishment::class);
    }

    /**
     * Get all interests for this organization's establishments.
     */
    public function establishmentInterests(): HasManyThrough
    {
        return $this->hasManyThrough(EstablishmentInterest::class, Establishment::class);
    }

    /**
     * Scope to filter only active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by sector.
     */
    public function scopeBySector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }

    /**
     * Get logo URL. Returns null if no logo is set.
     */
    public function getLogoUrlAttribute($value): ?string
    {
        return $value ? asset('storage/'.$value) : null;
    }

    /**
     * Get organization initials (first letter of name).
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    /**
     * Get the organization email (alias to contact_email).
     */
    public function getEmailAttribute(): ?string
    {
        return $this->contact_email;
    }

    /**
     * Get count of pending invitations.
     */
    public function getPendingInvitationsCountAttribute(): int
    {
        return $this->invitations()->where('status', 'pending')->count();
    }

    /**
     * Get count of accepted invitations (registered users).
     */
    public function getRegisteredUsersCountAttribute(): int
    {
        return $this->invitations()->where('status', 'accepted')->count();
    }

    /**
     * Get count of active users (logged in last 30 days).
     */
    public function getActiveUsersCountAttribute(): int
    {
        return $this->users()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
    }

    // --- SUBSCRIPTION HELPERS ---

    /**
     * Check if organization has an active subscription (not expired if paid).
     * Free plan is always considered "active" but limited.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === self::PLAN_FREE) {
            return true;
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    /**
     * Check if organization is on PRO plan or higher (Enterprise, Establishment).
     */
    public function isPro(): bool
    {
        return $this->hasActiveSubscription() &&
            in_array($this->subscription_plan, [self::PLAN_PRO, self::PLAN_ENTERPRISE, self::PLAN_ESTABLISHMENT]);
    }

    /**
     * Check if organization is on ENTERPRISE plan or Establishment plan.
     */
    public function isEnterprise(): bool
    {
        return $this->hasActiveSubscription() &&
            in_array($this->subscription_plan, [self::PLAN_ENTERPRISE, self::PLAN_ESTABLISHMENT]);
    }

    /**
     * Check if organization is on ESTABLISHMENT plan.
     */
    public function isEstablishment(): bool
    {
        return $this->hasActiveSubscription() && $this->subscription_plan === self::PLAN_ESTABLISHMENT;
    }

    /**
     * Get readable subscription status
     */
    public function getSubscriptionStatusLabelAttribute(): string
    {
        if ($this->subscription_plan === self::PLAN_FREE) {
            return 'Gratuit';
        }

        if ($this->subscription_plan === self::PLAN_ESTABLISHMENT) {
            return 'Établissement';
        }

        if (! $this->hasActiveSubscription()) {
            return 'Expiré';
        }

        return ucfirst($this->subscription_plan);
    }

    // --- INVITATION / MEMBER LIMITS ---

    /**
     * Limites du nombre total de membres invités (jeunes + mentors) par plan.
     * null = illimité (plan Établissement)
     */
    public const MEMBER_LIMITS = [
        self::PLAN_FREE         => 10,
        self::PLAN_PRO          => 20,
        self::PLAN_ENTERPRISE   => 50,
        self::PLAN_ESTABLISHMENT => null,
    ];

    /**
     * Get the maximum number of members allowed for the current plan.
     * Returns null if unlimited.
     */
    public function getMemberLimit(): ?int
    {
        // Priorité 1 : lire depuis le plan CreditPack en base (configurable via l'admin)
        $pack = \App\Models\CreditPack::where('type', 'subscription')
            ->where('target_plan', $this->subscription_plan)
            ->where('is_active', true)
            ->whereNotNull('member_limit')
            // Pour les plans multi-durée, on prend la limite du plan mensuel comme référence
            ->orderBy('duration_days')
            ->first();

        if ($pack && $pack->member_limit !== null) {
            return (int) $pack->member_limit;
        }

        // Priorité 2 : fallback sur les constantes si la DB n'est pas encore mise à jour
        // On utilise array_key_exists car ?? traite null comme "absent" et renverrait le fallback 10
        // pour le plan Établissement dont la limite est explicitement null (illimité)
        if (array_key_exists($this->subscription_plan, self::MEMBER_LIMITS)) {
            return self::MEMBER_LIMITS[$this->subscription_plan];
        }

        return 10; // Fallback sécurisé pour tout plan inconnu
    }

    /**
     * Get the current number of accepted members (jeunes + mentors).
     */
    public function getMemberCount(): int
    {
        return $this->users()
            ->whereIn('users.user_type', [User::TYPE_JEUNE, User::TYPE_MENTOR])
            ->count();
    }

    /**
     * Check if the organization can still invite more members.
     */
    public function canInviteMore(): bool
    {
        $limit = $this->getMemberLimit();

        // Unlimited plan
        if ($limit === null) {
            return true;
        }

        return $this->getMemberCount() < $limit;
    }

    /**
     * Get the number of remaining invitation slots.
     * Returns null if unlimited.
     */
    public function getRemainingSlots(): ?int
    {
        $limit = $this->getMemberLimit();

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->getMemberCount());
    }

    /**
     * Get a human-readable label for the member limit.
     */
    public function getMemberLimitLabelAttribute(): string
    {
        $limit = $this->getMemberLimit();

        return $limit === null ? 'Illimité' : (string) $limit;
    }
}


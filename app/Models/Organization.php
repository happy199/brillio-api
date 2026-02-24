<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    ];

    /**
     * Subscription Plans
     */
    public const PLAN_FREE = 'free';

    public const PLAN_PRO = 'pro';

    public const PLAN_ENTERPRISE = 'enterprise';

    /**
     * Get the attributes that should be cast.
     */
    protected $casts = [
        'status' => 'string',
        'subscription_expires_at' => 'datetime',
        'auto_renew' => 'boolean',
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
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role', 'referral_code_used'])
            ->withTimestamps();
    }

    /**
     * Get all mentors linked to this organization via the pivot table.
     */
    public function mentors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->where('users.user_type', 'mentor')
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
     * Check if organization is on PRO plan or higher (Enterprise).
     */
    public function isPro(): bool
    {
        return $this->hasActiveSubscription() &&
            in_array($this->subscription_plan, [self::PLAN_PRO, self::PLAN_ENTERPRISE]);
    }

    /**
     * Check if organization is on ENTERPRISE plan.
     */
    public function isEnterprise(): bool
    {
        return $this->hasActiveSubscription() && $this->subscription_plan === self::PLAN_ENTERPRISE;
    }

    /**
     * Get readable subscription status
     */
    public function getSubscriptionStatusLabelAttribute(): string
    {
        if ($this->subscription_plan === self::PLAN_FREE) {
            return 'Gratuit';
        }

        if (! $this->hasActiveSubscription()) {
            return 'Expiré';
        }

        return ucfirst($this->subscription_plan);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Organization extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'primary_color',
        'contact_email',
        'phone',
        'website',
        'sector',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'string',
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
                    $slug = $originalSlug . '-' . $counter;
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
     * Get all sponsored users (young users who registered via this organization's invitation).
     */
    public function sponsoredUsers(): HasMany
    {
        return $this->hasMany(User::class , 'sponsored_by_organization_id');
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
        return $value ? asset('storage/' . $value) : null;
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
        return $this->sponsoredUsers()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
    }
}
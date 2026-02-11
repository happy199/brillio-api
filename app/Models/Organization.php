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
                $organization->slug = Str::slug($organization->name);
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
     * Get logo URL with fallback to default.
     */
    public function getLogoUrlAttribute($value): string
    {
        return $value ?? asset('images/organization-placeholder.png');
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
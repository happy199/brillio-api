<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrganizationInvitation extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'referral_code',
        'invited_email',
        'status',
        'invited_at',
        'accepted_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (OrganizationInvitation $invitation) {
            if (empty($invitation->referral_code)) {
                $invitation->referral_code = self::generateUniqueReferralCode();
            }

            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Get the organization that owns this invitation.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who accepted this invitation (legacy relationship for single-use).
     */
    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class , 'referral_code', 'referral_code_used');
    }

    /**
     * Get all users who joined via this invitation.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class , 'referral_code_used', 'referral_code');
    }

    /**
     * Get the number of times this invitation has been used.
     */
    public function getUsesCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Generate a unique 8-character referral code.
     */
    public static function generateUniqueReferralCode(): string
    {
        do {
            // Generate 8-character alphanumeric code (uppercase)
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            // Auto-update status to expired
            $this->update(['status' => 'expired']);
            return true;
        }

        return false;
    }

    /**
     * Check if the invitation is still valid (pending and not expired).
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Mark invitation as used/accepted.
     */
    public function markAsAccepted(): void
    {
        // If it's a personal invitation (with email), mark as accepted to stop usage
        // If it's a generic link (no email), it remains "pending" to allow infinite usage
        if ($this->invited_email) {
            $this->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        }
        else {
            // Just update accepted_at for the first time it was used
            if (!$this->accepted_at) {
                $this->update(['accepted_at' => now()]);
            }
        }
    }

    /**
     * Scope to get only pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get non-expired invitations.
     */
    public function scopeNotExpired($query)
    {
        return $query->where('status', '!=', 'expired')
            ->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Get the invitation URL with referral code.
     */
    public function getInvitationUrlAttribute(): string
    {
        return route('jeune.register', ['ref' => $this->referral_code]);
    }
}
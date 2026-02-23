<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'credits_amount',
        'max_uses',
        'uses_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Users who have redeemed this coupon
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('credits_received', 'redeemed_at')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has already redeemed this coupon
     */
    public function hasBeenUsedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if coupon is valid (optionally for a specific user)
     */
    public function isValid(?User $user = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // If max_uses is set (not null and not 0), check total usage
        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        // If a user is provided, check if they've already used this coupon
        if ($user && $this->hasBeenUsedBy($user)) {
            return false;
        }

        return true;
    }
}

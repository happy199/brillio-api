<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'type', // credits, subscription
        'credits',
        'duration_days',
        'target_plan', // pro, enterprise
        'price',
        'promo_percent',
        'name',
        'description',
        'features', // json
        'is_active',
        'is_popular',
        'display_order'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    /**
     * Scope for credit packs only
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credits');
    }

    /**
     * Scope for subscription packs only
     */
    public function scopeSubscriptions($query)
    {
        return $query->where('type', 'subscription');
    }

    /**
     * Scope for specific user type
     */
    public function scopeForUserType($query, string $type)
    {
        return $query->where('user_type', $type);
    }
}
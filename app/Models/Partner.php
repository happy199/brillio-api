<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'website',
        'logo_path',
        'is_active',
        'display_order',
        'type',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope for active partners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered partners.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Accessor for full logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        return asset('storage/'.$this->logo_path);
    }

    /**
     * Accessor for initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }
}

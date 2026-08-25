<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Career extends Model
{
    protected $fillable = [
        'title',
        'description',
        'future_prospects',
        'african_context',
        'ai_impact_level',
        'ai_impact_explanation',
        'demand_level',
    ];

    /**
     * Get the MBTI types associated with this career.
     */
    public function mbtiTypes()
    {
        return $this->belongsToMany(self::class, 'career_mbti', 'career_id', 'mbti_type', 'id', 'mbti_type') // self::class is a dummy here just to map the pivot.
            ->withPivot('match_reason')
            ->withTimestamps();
    }

    /**
     * Get the MBTI types as flat array
     */
    public function getMbtiTypesListAttribute()
    {
        return DB::table('career_mbti')
            ->where('career_id', $this->id)
            ->pluck('mbti_type')
            ->toArray();
    }

    /**
     * Get sectors list
     */
    public function getSectorsListAttribute()
    {
        return DB::table('career_sector')
            ->where('career_id', $this->id)
            ->pluck('sector_code')
            ->toArray();
    }

    /**
     * Obtenir le libellé du niveau de demande traduit en français.
     */
    public function getDemandLevelLabelAttribute(): string
    {
        $raw = strtolower(trim($this->demand_level ?? ''));

        if (empty($raw)) {
            return '-';
        }

        $label = ucfirst($this->demand_level);
        if (str_contains($raw, 'high') || str_contains($raw, 'fort') || str_contains($raw, 'élév') || str_contains($raw, 'elev') || str_contains($raw, 'haut')) {
            $label = 'Élevée';
        } elseif (str_contains($raw, 'medium') || str_contains($raw, 'moyen')) {
            $label = 'Moyenne';
        } elseif (str_contains($raw, 'low') || str_contains($raw, 'faibl')) {
            $label = 'Faible';
        }

        return $label;
    }
}

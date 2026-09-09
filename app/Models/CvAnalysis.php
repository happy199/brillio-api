<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_token',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'candidate_name',
        'candidate_title',
        'candidate_contact',
        'parsed_content',
        'global_score',
        'status_label',
        'criteria_scores',
        'strengths',
        'improvements',
        'recommendations',
        'summary',
        'is_claimed',
    ];

    protected $casts = [
        'candidate_contact' => 'array',
        'parsed_content' => 'array',
        'criteria_scores' => 'array',
        'strengths' => 'array',
        'improvements' => 'array',
        'recommendations' => 'array',
        'global_score' => 'integer',
        'file_size' => 'integer',
        'is_claimed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcule le statut textuel en fonction du score
     */
    public static function determineStatusLabel(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Excellent',
            $score >= 70 => 'Très bien',
            $score >= 50 => 'Bon potentiel',
            default => 'À perfectionner',
        };
    }
}

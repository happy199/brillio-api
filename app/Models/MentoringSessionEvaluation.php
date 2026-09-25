<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringSessionEvaluation extends Model
{
    use HasFactory;

    protected $table = 'mentoring_session_evaluations';

    protected $fillable = [
        'mentoring_session_id',
        'mentee_id',
        'mentor_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * The mentoring session evaluated.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(MentoringSession::class, 'mentoring_session_id');
    }

    /**
     * The mentee (youth) who submitted the evaluation.
     */
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    /**
     * The mentor evaluated.
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}

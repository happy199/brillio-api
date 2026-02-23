<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentoringSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'is_paid',
        'price',
        'status', // proposed, pending_payment, confirmed, cancelled, completed
        'cancel_reason',
        'meeting_link',
        'report_content', // JSON: { progress, obstacles, smart_goals }
        'created_by', // mentor, mentee
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_paid' => 'boolean',
        'report_content' => 'array',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Participants (Mentees)
    public function mentees()
    {
        return $this->belongsToMany(User::class, 'mentoring_session_user', 'mentoring_session_id', 'user_id')
            ->withPivot('status', 'rejection_reason')
            ->withTimestamps();
    }

    /**
     * Get the Jitsi room name (meeting ID) from the link
     */
    public function getMeetingIdAttribute()
    {
        if (! $this->meeting_link) {
            return null;
        }

        return basename($this->meeting_link);
    }

    /**
     * Calculate the cost in credits based on the FCFA price
     */
    public function getCreditCostAttribute()
    {
        if (! $this->price) {
            return 0;
        }

        static $jeuneCreditPrice = null;

        if ($jeuneCreditPrice === null) {
            $jeuneCreditPrice = \App\Models\SystemSetting::where('key', 'credit_price_jeune')->value('value') ?? 50;
        }

        // Avoid division by zero
        if ($jeuneCreditPrice <= 0) {
            return (int) $this->price;
        }

        // Session Price (FCFA) / Credit Price (FCFA/Credit) = Credits needed
        // User requested floor behavior for integer credits
        return (int) floor($this->price / $jeuneCreditPrice);
    }

    // Translated status
    public function getTranslatedStatusAttribute()
    {
        return match ($this->status) {
            'proposed' => 'Proposée',
            'pending_payment' => 'En attente de paiement',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
            'completed' => 'Terminée',
            default => $this->status,
        };
    }
}

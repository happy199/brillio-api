<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonerooTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'moneroo_transaction_id',
        'amount',
        'currency',
        'status',
        'credits_amount',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction (polymorphic)
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}

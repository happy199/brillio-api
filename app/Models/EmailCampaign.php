<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'subject',
        'body',
        'type',
        'recipients_count',
        'sent_count',
        'failed_count',
        'status',
        'sent_by',
        'sent_at',
        'recipient_emails',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'recipient_emails' => 'array',
    ];

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_by');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}

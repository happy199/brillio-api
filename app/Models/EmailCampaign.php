<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'parent_id',
        'subject',
        'body',
        'type',
        'status',
        'is_recurring',
        'frequency',
        'start_date',
        'end_date',
        'next_run_at',
        'last_run_at',
        'recipient_filters',
        'recipients_count',
        'sent_count',
        'failed_count',
        'sent_by',
        'sent_at',
        'recipient_emails',
        'attachments',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipient_emails' => 'array',
        'recipient_filters' => 'array',
        'attachments' => 'array',
    ];

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'parent_id');
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

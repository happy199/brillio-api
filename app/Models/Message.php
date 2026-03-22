<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'mentorship_id',
        'sender_id',
        'body',
        'original_body',
        'is_flagged',
        'flag_reason',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'read_at',
        'edited_at',
        'is_deleted',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }
}

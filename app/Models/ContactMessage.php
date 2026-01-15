<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'read_at',
        'replied_at',
        'replied_by',
        'reply_message',
        'ip_address',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'replied_by');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function markAsRead()
    {
        if ($this->status === 'new') {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    public function markAsReplied($userId, $replyMessage)
    {
        $this->update([
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => $userId,
            'reply_message' => $replyMessage,
        ]);
    }
}

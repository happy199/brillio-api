<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaInstallation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_uuid',
        'platform',
        'browser',
        'user_agent',
        'ip_address',
        'installed_at',
        'last_active_at',
        'launches_count',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'last_active_at' => 'datetime',
        'launches_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

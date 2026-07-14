<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommercialActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercial_id',
        'assignable_type',
        'assignable_id',
        'status',
        'summary',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function assignable()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledTaskLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    const CREATED_AT = 'run_at';

    protected $fillable = [
        'command',
        'status',
        'duration',
        'output',
        'run_at',
    ];

    protected $casts = [
        'run_at' => 'datetime',
        'duration' => 'float',
    ];
}

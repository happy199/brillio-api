<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'credits',
        'price',
        'promo_percent',
        'name',
        'description',
        'is_active',
        'is_popular',
        'display_order'
    ];
}

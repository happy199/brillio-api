<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceView extends Model
{
    protected $fillable = ['user_id', 'resource_id', 'viewed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}

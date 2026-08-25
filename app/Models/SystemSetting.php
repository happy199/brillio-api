<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget('system_setting_'.$setting->key);
        });

        static::deleted(function ($setting) {
            Cache::forget('system_setting_'.$setting->key);
        });
    }

    public static function get(string $key, $default = null)
    {
        return static::getValue($key, $default);
    }

    public static function getValue(string $key, $default = null)
    {
        return Cache::remember('system_setting_'.$key, 86400, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (! $setting) {
                return $default;
            }

            return $setting->castValue();
        });
    }

    public function castValue()
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}

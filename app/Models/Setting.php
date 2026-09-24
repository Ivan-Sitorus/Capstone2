<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['setting_key', 'setting_value', 'group'];

    public static function get(string $key, $default = null): ?string
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return static::where('setting_key', $key)->value('setting_value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        // Normalize: DB column is NOT NULL, so null → empty string
        if ($value === null) {
            $value = '';
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (! is_string($value)) {
            $value = (string) $value;
        }

        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'group' => $group],
        );
        Cache::forget("setting_{$key}");
    }
}

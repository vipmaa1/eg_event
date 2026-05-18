<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemConfig
{
    private const CACHE_KEY = 'system_config';
    private const CACHE_TTL = 3600;

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SystemSetting::all()->keyBy('key')->map(function ($s) {
                return ['value' => $s->value, 'type' => $s->type];
            })->toArray();
        });

        $entry = $settings[$key] ?? null;

        if ($entry === null) {
            return $default;
        }

        return match ($entry['type']) {
            'boolean' => filter_var($entry['value'], FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $entry['value'],
            'float'   => (float) $entry['value'],
            default   => $entry['value'],
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type, 'group' => $group]
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

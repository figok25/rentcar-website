<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan situs berbentuk key => value yang dikelola admin lewat CMS.
 * Seluruh nilai disimpan sebagai teks; gunakan get(), bool(), atau set() agar cache tetap konsisten.
 */
#[Fillable(['key', 'value', 'group'])]
class WebsiteSetting extends Model
{
    public const CACHE_KEY = 'website_settings.all';

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * @return array<string, string|null>
     */
    public static function allSettings(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all(),
        );
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::allSettings();

        $value = $all[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function set(string $key, string|bool|null $value, ?string $group = null): static
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $attributes = ['value' => $value];

        if ($group !== null) {
            $attributes['group'] = $group;
        }

        return static::query()->updateOrCreate(['key' => $key], $attributes);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

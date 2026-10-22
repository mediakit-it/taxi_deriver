<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    /**
     * Все настройки одним массивом key => value, с кэшем.
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('settings.all', function () {
            if (!Schema::hasTable('settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Значение одной настройки по ключу.
     */
    public static function get(string $key, $default = null)
    {
        $settings = static::allCached();

        return $settings[$key] ?? $default;
    }

    /**
     * Сохранить значение настройки (создаёт ключ при отсутствии).
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}

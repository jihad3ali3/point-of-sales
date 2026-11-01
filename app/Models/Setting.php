<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'outlet_id',
        'value',
        'description',
    ];

    /**
     * Get the global (outlet-agnostic) value of a setting.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->whereNull('outlet_id')->first();

        return $setting ? $setting->value : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    /**
     * Outlet value when present, otherwise the global value.
     */
    public static function getForOutlet(string $key, ?Outlet $outlet, $default = null)
    {
        if ($outlet) {
            $value = static::where('key', $key)->where('outlet_id', $outlet->id)->value('value');
            if ($value !== null) {
                return $value;
            }
        }

        return static::get($key, $default);
    }

    public static function getBoolForOutlet(string $key, ?Outlet $outlet, bool $default = false): bool
    {
        return filter_var(static::getForOutlet($key, $outlet, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    public static function setForOutlet(string $key, $value, ?Outlet $outlet, ?string $description = null)
    {
        if (! $outlet) {
            return static::set($key, $value, $description);
        }

        return static::updateOrCreate(
            ['key' => $key, 'outlet_id' => $outlet->id],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key, 'outlet_id' => null],
            ['value' => $value, 'description' => $description]
        );
    }

    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $payload) {
            static::set(
                $key,
                $payload['value'] ?? null,
                $payload['description'] ?? null
            );
        }
    }
}

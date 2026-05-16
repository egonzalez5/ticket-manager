<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        if (!$row) return $default;
        $decoded = json_decode($row->value, true);
        return $decoded !== null ? $decoded : $row->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) ? (string) $value : json_encode($value)]
        );
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }

    public static function getGroup(string $prefix, array $defaults = []): array
    {
        $rows = static::where('key', 'like', "{$prefix}.%")
            ->pluck('value', 'key')
            ->toArray();

        $result = [];
        foreach ($defaults as $key => $default) {
            $fullKey      = "{$prefix}.{$key}";
            $raw          = $rows[$fullKey] ?? null;
            $result[$key] = $raw !== null
                ? (json_decode($raw, true) ?? $raw)
                : $default;
        }
        return $result;
    }
}

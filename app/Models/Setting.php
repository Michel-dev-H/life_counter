<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function read(string $key): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value === null ? null : (string) $value;
    }

    public static function write(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

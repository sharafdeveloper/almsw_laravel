<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function set(string $key, $value): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $v = static::get($key, null);
        return $v === null ? $default : (float) $v;
    }
}

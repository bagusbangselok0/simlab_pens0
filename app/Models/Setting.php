<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const AUTO_APPROVE_KALAB = 'approval.auto_approve_kalab';

    protected $fillable = [
        'setting_key',
        'scope_key',
        'value',
        'value_type',
        'description',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function labScope(int $labId): string
    {
        return 'lab:' . $labId;
    }

    public static function boolean(string $key, string $scopeKey = 'global', bool $default = false): bool
    {
        $setting = static::query()
            ->where('setting_key', $key)
            ->where('scope_key', $scopeKey)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return $default;
        }

        return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
    }
}
<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    public const AUTO_APPROVE_KALAB = 'approval.auto_approve_kalab';

    // Pengaturan Kebijakan Peminjaman Alat
    public const MAX_ITEMS_PER_REQUEST = 'alat.max_items_per_request';
    public const MAX_LOAN_DAYS         = 'alat.max_loan_days';
    public const MAX_ACTIVE_LOANS      = 'alat.max_active_loans';
    public const ALLOW_MANUAL_INPUT    = 'alat.allow_manual_input';

    // Pengaturan Gateway WhatsApp (Fonnte)
    public const WA_ENABLED                 = 'notification.wa_enabled';
    public const WA_TOKEN                   = 'notification.wa_token';
    public const WA_API_URL                 = 'notification.wa_api_url';
    public const WA_NOTIF_PEMINJAMAN_LAB    = 'notification.wa_peminjaman_lab';
    public const WA_NOTIF_PEMINJAMAN_ALAT   = 'notification.wa_peminjaman_alat';

    // Pengaturan Gateway Web Push (VAPID)
    public const PUSH_ENABLED               = 'notification.push_enabled';
    public const VAPID_SUBJECT              = 'notification.vapid_subject';
    public const VAPID_PUBLIC_KEY           = 'notification.vapid_public_key';
    public const VAPID_PRIVATE_KEY          = 'notification.vapid_private_key';
    public const PUSH_NOTIF_PEMINJAMAN_LAB  = 'notification.push_peminjaman_lab';
    public const PUSH_NOTIF_PEMINJAMAN_ALAT = 'notification.push_peminjaman_alat';

    // Pengaturan Tema & Loader
    public const THEME_DEFAULT_MODE    = 'theme.default_mode';
    public const THEME_ENABLE_LOADER   = 'theme.enable_loader';
    public const THEME_LOADER_SPEED    = 'theme.loader_speed';
    public const THEME_ACCENT_COLOR    = 'theme.accent_color';
    public const THEME_SIDEBAR_DEFAULT = 'theme.sidebar_default';

    public const SECRET_KEYS = [
        self::WA_TOKEN,
        self::VAPID_PRIVATE_KEY,
    ];

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

    public static function decryptValue(?string $rawValue): string
    {
        if ($rawValue === null || $rawValue === '') {
            return '';
        }

        try {
            return Crypt::decryptString($rawValue);
        } catch (DecryptException $e) {
            $decoded = json_decode((string) base64_decode($rawValue, true), true);
            if (is_array($decoded) && isset($decoded['iv'], $decoded['value'], $decoded['mac'])) {
                Log::warning('Setting::decryptValue gagal mendekripsi payload setting (kemungkinan APP_KEY berubah).');
                return '';
            }

            // Fallback aman untuk nilai legacy plaintext sebelum enkripsi diterapkan
            return $rawValue;
        }
    }

    public static function putEncrypted(
        string $key,
        ?string $plainValue,
        string $description = '',
        ?int $updatedBy = null,
        string $scopeKey = 'global'
    ): self {
        $trimmed = trim((string) $plainValue);
        $storedValue = $trimmed !== '' ? Crypt::encryptString($trimmed) : '';

        return static::updateOrCreate(
            [
                'setting_key' => $key,
                'scope_key' => $scopeKey,
            ],
            [
                'value' => $storedValue,
                'value_type' => 'encrypted',
                'description' => $description,
                'is_active' => true,
                'updated_by' => $updatedBy,
            ]
        );
    }

    public static function hasConfiguredSecret(string $key, string $scopeKey = 'global'): bool
    {
        $setting = static::query()
            ->where('setting_key', $key)
            ->where('scope_key', $scopeKey)
            ->where('is_active', true)
            ->first();

        if (!$setting || $setting->value === null || $setting->value === '') {
            return false;
        }

        return trim(static::decryptValue($setting->value)) !== '';
    }

    public static function get(string $key, string $scopeKey = 'global', mixed $default = null): mixed
    {
        $setting = static::query()
            ->where('setting_key', $key)
            ->where('scope_key', $scopeKey)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return $default;
        }

        if ($setting->value_type === 'encrypted' || in_array($key, self::SECRET_KEYS, true)) {
            $decrypted = static::decryptValue($setting->value);

            return $decrypted !== '' ? $decrypted : $default;
        }

        return match ($setting->value_type) {
            'integer', 'int' => (int) $setting->value,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function boolean(string $key, string $scopeKey = 'global', bool $default = false): bool
    {
        return (bool) static::get($key, $scopeKey, $default);
    }

    public static function int(string $key, string $scopeKey = 'global', int $default = 0): int
    {
        return (int) static::get($key, $scopeKey, $default);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenLogbookAutoApprove extends Model
{
    protected $table = 'dosen_logbook_auto_approves';

    protected $fillable = [
        'dosen_id',
        'plp_id',
        'is_enabled',
        'valid_until',
        'consented_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'valid_until' => 'date',
            'consented_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function plp(): BelongsTo
    {
        return $this->belongsTo(User::class, 'plp_id');
    }

    /**
     * Cek apakah dosen mengizinkan auto-approve logbook untuk PLP tertentu pada tanggal hari ini.
     */
    public static function isActiveFor(int $dosenId, int $plpId): bool
    {
        $today = now('Asia/Jakarta')->toDateString();

        return self::query()
            ->where('dosen_id', $dosenId)
            ->where('is_enabled', true)
            ->whereNotNull('consented_at')
            ->where(function ($q) use ($plpId) {
                $q->where('plp_id', $plpId)
                    ->orWhereNull('plp_id');
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $today);
            })
            ->exists();
    }
}

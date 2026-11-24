<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeJadwal extends Model
{
    protected $table = 'periode_jadwals';

    protected $fillable = [
        'nama_periode',
        'semester',
        'tahun_ajaran',
        'is_active',
        'source_filename',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function jadwalLabs(): HasMany
    {
        return $this->hasMany(JadwalLab::class, 'periode_jadwal_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

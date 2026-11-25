<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookPraktikum extends Model
{
    protected $table = 'logbook_praktikums';

    protected $fillable = [
        'jadwal_lab_id',
        'lab_id',
        'plp_id',
        'dosen_id',
        'nama_dosen_fallback',
        'nama_mk',
        'kelas',
        'hari',
        'semester',
        'tahun_akademik',
        'jam_default',
        'keterangan_plp',
        'default_mahasiswa_user_id',
        'default_mahasiswa_nama',
        'default_mahasiswa_nrp',
        'default_mahasiswa_ttd_path',
    ];

    public function jadwalLab(): BelongsTo
    {
        return $this->belongsTo(JadwalLab::class, 'jadwal_lab_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function plp(): BelongsTo
    {
        return $this->belongsTo(User::class, 'plp_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function defaultMahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_mahasiswa_user_id');
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(LogbookPertemuan::class, 'logbook_praktikum_id')
            ->orderBy('minggu_ke')
            ->orderBy('tanggal_pelaksanaan');
    }

    public function getDisplayDosenNameAttribute(): string
    {
        if ($this->dosen) {
            return trim($this->dosen->full_name);
        }

        return $this->nama_dosen_fallback ?: '-';
    }

    public function getDisplayPlpNameAttribute(): string
    {
        $base = trim($this->plp?->full_name ?? '-');
        if ($this->keterangan_plp) {
            return $base . ' (' . $this->keterangan_plp . ')';
        }

        return $base;
    }

    public function getDefaultMahasiswaTtdUrlAttribute(): ?string
    {
        if (!$this->default_mahasiswa_ttd_path) {
            return null;
        }

        return route('signatures.logbook.show', [
            'type' => 'logbook',
            'id' => $this->id,
            'v' => substr(md5($this->default_mahasiswa_ttd_path), 0, 8),
        ]);
    }
}

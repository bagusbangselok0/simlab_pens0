<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookPertemuan extends Model
{
    protected $table = 'logbook_pertemuans';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_DOSEN = 'pending_dosen';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const KATEGORI_ALAT = [
        'Alat 1',
        'Alat 2',
        'Alat 3',
        'Bahan Umum',
        'Bahan Khusus',
    ];

    protected $fillable = [
        'logbook_praktikum_id',
        'minggu_ke',
        'judul_praktikum',
        'tanggal_pelaksanaan',
        'jam_pelaksanaan',
        'peralatan',
        'aktivitas',
        'permasalahan',
        'mahasiswa_user_id',
        'mahasiswa_nama',
        'mahasiswa_nrp',
        'mahasiswa_ttd_path',
        'status',
        'approval_mode',
        'submitted_at',
        'approved_at',
        'rejection_note',
        'revoked_at',
        'revoked_reason',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
            'peralatan' => 'array',
            'aktivitas' => 'array',
            'permasalahan' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(LogbookPraktikum::class, 'logbook_praktikum_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_user_id');
    }

    public function hasPeralatan(): bool
    {
        return collect($this->peralatan ?? [])
            ->contains(fn ($row) => trim((string) ($row['nama_alat'] ?? '')) !== '');
    }

    /**
     * Mengembalikan identitas & TTD perwakilan mahasiswa efektif (override pertemuan atau default buku 1 semester).
     */
    public function getEffectiveMahasiswaAttribute(): array
    {
        $hasOverride = !empty($this->mahasiswa_nama) || !empty($this->mahasiswa_ttd_path) || !empty($this->mahasiswa_user_id);

        if ($hasOverride) {
            $nama = $this->mahasiswa_nama ?: ($this->mahasiswa?->nama_asli ?? '');
            $nrp = $this->mahasiswa_nrp ?: ($this->mahasiswa?->nrp ?? '');
            $ttdPath = $this->mahasiswa_ttd_path;
            $source = 'pertemuan';
        } else {
            $book = $this->logbook;
            $nama = $book?->default_mahasiswa_nama ?: ($book?->defaultMahasiswa?->nama_asli ?? '');
            $nrp = $book?->default_mahasiswa_nrp ?: ($book?->defaultMahasiswa?->nrp ?? '');
            $ttdPath = $book?->default_mahasiswa_ttd_path;
            $source = ($nama !== '' || $ttdPath) ? 'semester' : 'none';
        }

        $displayLabel = trim($nama);
        if ($displayLabel !== '' && trim((string) $nrp) !== '') {
            $displayLabel .= ' / ' . trim((string) $nrp);
        } elseif ($displayLabel === '' && trim((string) $nrp) !== '') {
            $displayLabel = trim((string) $nrp);
        }

        return [
            'nama' => $nama,
            'nrp' => $nrp,
            'display' => $displayLabel,
            'ttd_path' => $ttdPath,
            'ttd_url' => $ttdPath ? asset('storage/logbook_signatures/' . $ttdPath) : null,
            'source' => $source,
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_DOSEN => 'Menunggu TTD Dosen',
            self::STATUS_APPROVED => $this->approval_mode === 'auto' ? 'Disetujui (Auto)' : 'Disetujui',
            self::STATUS_REJECTED => $this->revoked_at ? 'TTD Dibatalkan' : 'Perlu Revisi',
            default => 'Draft',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_DOSEN => 'bg-warning text-dark',
            self::STATUS_APPROVED => $this->approval_mode === 'auto' ? 'bg-info text-dark' : 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}

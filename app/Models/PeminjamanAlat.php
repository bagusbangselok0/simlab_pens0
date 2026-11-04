<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanAlat extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_alats';

    protected $fillable = [
        'kode_peminjaman',
        'mahasiswa_id',
        'lab_id',
        'plp_id',
        'keperluan',
        'waktu_pinjam',
        'rencana_kembali',
        'waktu_kembali',
        'status',
        'catatan_penolakan',
        'alasan_batal',
        'ttd_mahasiswa_file',
        'ttd_plp_file',
        'tgl_ttd_plp',
        'kondisi_kembali_catatan',
    ];

    protected $casts = [
        'waktu_pinjam' => 'datetime',
        'rencana_kembali' => 'datetime',
        'waktu_kembali' => 'datetime',
        'tgl_ttd_plp' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function plp()
    {
        return $this->belongsTo(User::class, 'plp_id');
    }

    public function details()
    {
        return $this->hasMany(PeminjamanAlatDetail::class, 'peminjaman_alat_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending_plp' => '<span class="badge bg-warning">Menunggu PLP</span>',
            'disetujui' => '<span class="badge bg-success">Disetujui</span>',
            'ditolak' => '<span class="badge bg-danger">Ditolak</span>',
            'sedang_dipinjam' => '<span class="badge bg-primary">Sedang Dipinjam</span>',
            'selesai' => '<span class="badge bg-info">Selesai / Dikembalikan</span>',
            'dibatalkan' => '<span class="badge bg-secondary">Dibatalkan</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiLab extends Model
{
    protected $table = 'presensi_lab';

    protected $fillable = [
        'peminjaman_lab_id',
        'mahasiswa_id',
        'tanggal_presensi',
        'satpam_masuk_id',
        'satpam_keluar_id',
        'jam_masuk',
        'jam_keluar',
        'status_presensi'
    ];

    // Casting agar otomatis jadi objek Carbon (untuk manipulasi waktu)
    protected $casts = [
        'tanggal_presensi' => 'date:Y-m-d',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
    ];

    public function peminjamanLab()
    {
        return $this->belongsTo(PeminjamanLab::class, 'peminjaman_lab_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function satpamMasuk()
    {
        return $this->belongsTo(User::class, 'satpam_masuk_id');
    }

    public function satpamKeluar()
    {
        return $this->belongsTo(User::class, 'satpam_keluar_id');
    }
}

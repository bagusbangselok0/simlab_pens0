<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanLab extends Model
{
    protected $table = 'peminjaman_lab';

    protected $fillable = [
        'mahasiswa_id',
        'lab_id',
        'lab_manager_id',
        'tujuan',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'ttd_mahasiswa_file',
        'ttd_plp_file',
        'ttd_kalab_file'
    ];
    // Casting agar otomatis jadi objek Carbon (untuk manipulasi waktu)
    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'tgl_ttd_plp' => 'datetime',
        'tgl_ttd_kalab' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function labManager()
    {
        return $this->belongsTo(LabManager::class, 'lab_manager_id');
    }

    public function presensi()
    {
        return $this->hasMany(PresensiLab::class, 'peminjaman_lab_id');
    }

    public function presensiTerakhir()
    {
        return $this->hasOne(PresensiLab::class, 'peminjaman_lab_id')->latestOfMany();
    }

    public function presensiHariIni()
    {
        return $this->hasOne(PresensiLab::class, 'peminjaman_lab_id')
            ->where('tanggal_presensi', now('Asia/Jakarta')->toDateString());
    }
}

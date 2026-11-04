<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    protected $table = 'labs';

    protected $fillable = [
        'nama_lab',
        'kode_lab',
        'lokasi',
        'bisa_dipinjam_ruangan',
    ];

    protected $casts = [
        'bisa_dipinjam_ruangan' => 'boolean',
    ];

    // Relasi ke PeminjamanLab
    public function peminjamanLabs()
    {
        return $this->hasMany(PeminjamanLab::class);
    }

    // Relasi ke PeminjamanAlat
    public function peminjamanAlats()
    {
        return $this->hasMany(PeminjamanAlat::class);
    }

    // Relasi ke LabManager
    public function labManager()
    {
        return $this->hasOne(LabManager::class);
    }

    // Relasi ke InventarisRuangan
    public function inventarisRuangans()
    {
        return $this->hasMany(InventarisRuangan::class);
    }
}

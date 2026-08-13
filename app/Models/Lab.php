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
    ];

    // Relasi ke PeminjamanLab
    public function peminjamanLabs() {
        return $this->hasMany(PeminjamanLab::class);
    }

    // Relasi ke LabManager
    public function labManager() {
        return $this->hasOne(LabManager::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabManager extends Model
{
    protected $table = 'lab_managers';

    protected $fillable = [
        'lab_id',
        'plp_id',
        'kalab_id',
    ];

    // Relasi ke Lab
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    // Relasi ke User
    public function plp()
    {
        return $this->belongsTo(User::class);
    }

    public function kalab()
    {
        return $this->belongsTo(User::class);
    }

    public function peminjamanLabs()
    {
        return $this->hasMany(PeminjamanLab::class, 'lab_manager_id');
    }
}

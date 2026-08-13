<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatans';

    protected $fillable = [
        'nama_jabatan',
        'slug',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'jabatan_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlpAktivitasTemplate extends Model
{
    protected $table = 'plp_aktivitas_templates';

    protected $fillable = [
        'plp_id',
        'nama_template',
        'aktivitas',
    ];

    protected function casts(): array
    {
        return [
            'aktivitas' => 'array',
        ];
    }

    public function plp(): BelongsTo
    {
        return $this->belongsTo(User::class, 'plp_id');
    }
}

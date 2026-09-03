<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisRuangan extends Model
{
    use HasFactory;

    protected $table = 'inventaris_ruangans';

    protected $fillable = [
        'inventaris_barang_id',
        'lab_id',
        'kode_barang',
        'nama_barang',
        'spesifikasi_merk_tipe',
        'tahun_perolehan',
        'jumlah',
        'satuan',
        'kondisi',
        'nup',
        'is_bisa_dipinjam',
        'keterangan',
        'foto_barang',
    ];

    protected function casts(): array
    {
        return [
            'tahun_perolehan' => 'integer',
            'jumlah' => 'integer',
            'is_bisa_dipinjam' => 'boolean',
        ];
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function inventarisBarang()
    {
        return $this->belongsTo(inventarisBarang::class, 'inventaris_barang_id');
    }

    public function getKondisiLabelAttribute(): string
    {
        return match ($this->kondisi) {
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            default => ucfirst(str_replace('_', ' ', $this->kondisi ?? 'baik')),
        };
    }

    public function getKondisiBadgeClassAttribute(): string
    {
        return match ($this->kondisi) {
            'baik' => 'badge bg-success',
            'rusak_ringan' => 'badge bg-warning text-dark',
            'rusak_berat' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisBarang extends Model
{
    use HasFactory;

    protected $table = 'inventaris_barangs';

    protected $fillable = [
        'kode_barang',
        'nup',
        'nama_barang',
        'merk',
        'tipe',
        'tgl_buku_pertama',
        'tgl_perolehan',
        'spesifikasi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_buku_pertama' => 'date:Y-m-d',
            'tgl_perolehan' => 'date:Y-m-d',
        ];
    }

    public function inventarisRuangan()
    {
        return $this->hasOne(InventarisRuangan::class, 'inventaris_barang_id');
    }

    public function getMerkTipeAttribute(): string
    {
        $parts = array_filter([$this->merk, $this->tipe]);
        return count($parts) > 0 ? implode(' - ', $parts) : '-';
    }

    public function getAssignedDirAttribute(): ?InventarisRuangan
    {
        if ($this->inventarisRuangan) {
            return $this->inventarisRuangan;
        }

        $nup = trim((string) $this->nup);
        if ($nup === '') {
            return null;
        }

        $identity = $this->merk_tipe !== '-' ? $this->merk_tipe : ($this->spesifikasi ?? null);

        return InventarisRuangan::where('kode_barang', $this->kode_barang)
            ->where('nama_barang', $this->nama_barang)
            ->where('spesifikasi_merk_tipe', $identity)
            ->where('tahun_perolehan', $this->tgl_perolehan?->year)
            ->where(function ($query) use ($nup) {
                $query->where('nup', $nup)
                    ->orWhere('nup', 'like', $nup . ',%')
                    ->orWhere('nup', 'like', '%, ' . $nup)
                    ->orWhere('nup', 'like', '%,' . $nup . ',%')
                    ->orWhere('nup', 'like', '%,' . $nup)
                    ->orWhere('nup', 'like', $nup . '%');
            })
            ->first();
    }

    public function getIsAssignedAttribute(): bool
    {
        return $this->assigned_dir !== null;
    }
}

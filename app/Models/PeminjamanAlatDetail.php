<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanAlatDetail extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_alat_details';

    protected $fillable = [
        'peminjaman_alat_id',
        'inventaris_ruangan_id',
        'nama_alat',
        'kode_barang',
        'nup',
        'jumlah',
        'satuan',
        'is_manual',
        'spesifikasi',
        'keterangan',
        'kondisi_pinjam',
        'kondisi_kembali',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'is_manual' => 'boolean',
    ];

    public function peminjamanAlat()
    {
        return $this->belongsTo(PeminjamanAlat::class, 'peminjaman_alat_id');
    }

    public function inventarisRuangan()
    {
        return $this->belongsTo(InventarisRuangan::class, 'inventaris_ruangan_id');
    }
}


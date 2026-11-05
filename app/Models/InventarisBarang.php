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
        'jenis_barang',
        'foto_barang',
        'sumber_dana',
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

        // 1. Coba pencarian langsung via SQL (mendukung berbagai variasi spasi & koma)
        $query = InventarisRuangan::with('lab')
            ->where('kode_barang', $this->kode_barang)
            ->where('nama_barang', $this->nama_barang);

        if ($identity) {
            $query->where(function ($q) use ($identity) {
                $q->where('spesifikasi_merk_tipe', $identity)
                  ->orWhereNull('spesifikasi_merk_tipe')
                  ->orWhere('spesifikasi_merk_tipe', '-');
            });
        }

        if ($this->tgl_perolehan) {
            $query->where(function ($q) {
                $q->where('tahun_perolehan', $this->tgl_perolehan->year)
                  ->orWhereNull('tahun_perolehan');
            });
        }

        $directMatch = (clone $query)->where(function ($q) use ($nup) {
            $q->where('nup', $nup)
                ->orWhere('nup', 'like', $nup . ',%')
                ->orWhere('nup', 'like', $nup . ' ,%')
                ->orWhere('nup', 'like', '%, ' . $nup . ',%')
                ->orWhere('nup', 'like', '%,' . $nup . ',%')
                ->orWhere('nup', 'like', '%, ' . $nup . ' ,%')
                ->orWhere('nup', 'like', '%, ' . $nup)
                ->orWhere('nup', 'like', '%,' . $nup);
        })->first();

        if ($directMatch) {
            return $directMatch;
        }

        // 2. Fallback PHP: cocokkan NUP (mendukung rentang seperti "1-4" dan zero-padding)
        $candidates = $query->get();
        foreach ($candidates as $candidate) {
            if ($this->matchesNupString($candidate->nup, $nup)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah NUP target terdapat dalam string NUP DIR
     * (mendukung pemisah koma, spasi, rentang 1-4, dan perbedaan leading zero).
     */
    public function matchesNupString(?string $dirNupStr, ?string $targetNup): bool
    {
        if (empty($dirNupStr) || empty($targetNup)) {
            return false;
        }

        $targetTrimmed = trim((string) $targetNup);
        $targetInt = is_numeric($targetTrimmed) ? (int) $targetTrimmed : null;

        $parts = preg_split('/\s*,\s*/', trim($dirNupStr), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === $targetTrimmed) {
                return true;
            }
            if ($targetInt !== null && is_numeric($part) && (int) $part === $targetInt) {
                return true;
            }

            // Format rentang seperti "1-4" atau "01 - 04"
            if (preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $part, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                if ($targetInt !== null && $targetInt >= $start && $targetInt <= $end) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getIsAssignedAttribute(): bool
    {
        return $this->assigned_dir !== null;
    }
}

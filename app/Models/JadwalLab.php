<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalLab extends Model
{
    protected $table = 'jadwal_labs';

    public const HARI_ORDER = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at"];

    public const JAM_KE_MAP = [
        1  => ['start' => '08.00', 'end' => '08.50'],
        2  => ['start' => '08.50', 'end' => '09.40'],
        3  => ['start' => '09.40', 'end' => '10.30'],
        4  => ['start' => '10.30', 'end' => '11.20'],
        5  => ['start' => '11.20', 'end' => '12.10'],
        6  => ['start' => '13.00', 'end' => '13.50'],
        7  => ['start' => '13.50', 'end' => '14.40'],
        8  => ['start' => '14.40', 'end' => '15.30'],
        9  => ['start' => '15.30', 'end' => '16.20'],
        10 => ['start' => '16.20', 'end' => '17.10'],
    ];

    protected $fillable = [
        'periode_jadwal_id',
        'lab_id',
        'nama_ruangan',
        'hari',
        'jam_ke_mulai',
        'jam_ke_selesai',
        'waktu_mulai',
        'waktu_berakhir',
        'mata_kuliah',
        'jenis',
        'kelas',
        'semester',
        'program_studi',
        'dosen_id',
        'nama_dosen',
        'catatan',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeJadwal::class, 'periode_jadwal_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function getDisplayDosenAttribute(): string
    {
        return $this->dosen?->full_name ?: ($this->nama_dosen ?: '-');
    }

    /**
     * Urutkan ruangan secara natural: Lab L-201..204 dulu, lalu ruangan lainnya.
     */
    public static function sortRoomNames(array $rooms): array
    {
        usort($rooms, function (string $a, string $b) {
            $rankA = str_starts_with($a, 'Lab ') ? 0 : (stripos($a, 'Studio') !== false ? 1 : 2);
            $rankB = str_starts_with($b, 'Lab ') ? 0 : (stripos($b, 'Studio') !== false ? 1 : 2);

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strnatcasecmp($a, $b);
        });

        return array_values($rooms);
    }

    public static function hariOrderIndex(string $hari): int
    {
        $idx = array_search($hari, self::HARI_ORDER, true);
        return $idx === false ? 99 : $idx;
    }
}

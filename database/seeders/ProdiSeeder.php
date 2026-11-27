<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now(tz: 'Asia/Jakarta');

        $jurusanTi = DB::table('jurusan')
            ->where('nama_jurusan', 'Departemen Teknik Informatika dan Komputer')
            ->value('id') ?? 1;
        $jurusanMmk = DB::table('jurusan')
            ->where('nama_jurusan', 'Departemen Teknologi Multimedia Kreatif')
            ->value('id') ?? 2;

        $prodis = [
            1 => [
                'nama_prodi' => 'Teknik Informatika',
                'kode_prodi' => 'TI',
                'jurusan_id' => (int) $jurusanTi,
            ],
            2 => [
                'nama_prodi' => 'Multimedia Broadcasting',
                'kode_prodi' => 'MMB',
                'jurusan_id' => (int) $jurusanMmk,
            ],
        ];

        foreach ($prodis as $id => $item) {
            $existing = DB::table('prodi')->where('kode_prodi', $item['kode_prodi'])->first();

            if ($existing) {
                $updates = [
                    'nama_prodi' => $item['nama_prodi'],
                    'jurusan_id' => $item['jurusan_id'],
                    'updated_at' => $now,
                ];

                if (
                    (int) $existing->id !== $id
                    && DB::table('users')->where('prodi_id', $existing->id)->doesntExist()
                    && DB::table('prodi')->where('id', $id)->doesntExist()
                ) {
                    $updates['id'] = $id;
                }

                DB::table('prodi')->where('id', $existing->id)->update($updates);
                continue;
            }

            DB::table('prodi')->updateOrInsert(
                ['id' => $id],
                [
                    'nama_prodi' => $item['nama_prodi'],
                    'kode_prodi' => $item['kode_prodi'],
                    'jurusan_id' => $item['jurusan_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

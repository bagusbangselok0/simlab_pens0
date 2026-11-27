<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now(tz: 'Asia/Jakarta');
        $jurusans = [
            1 => 'Departemen Teknik Informatika dan Komputer',
            2 => 'Departemen Teknologi Multimedia Kreatif',
            3 => 'Departemen Teknik Elektro',
            4 => 'Departemen Teknik Mekanika dan Energi',
        ];

        foreach ($jurusans as $id => $nama) {
            $existing = DB::table('jurusan')->where('nama_jurusan', $nama)->first();

            if ($existing) {
                $updates = [
                    'kode_jurusan' => null,
                    'updated_at' => $now,
                ];

                if (
                    (int) $existing->id !== $id
                    && DB::table('prodi')->where('jurusan_id', $existing->id)->doesntExist()
                    && DB::table('jurusan')->where('id', $id)->doesntExist()
                ) {
                    $updates['id'] = $id;
                }

                DB::table('jurusan')->where('id', $existing->id)->update($updates);
                continue;
            }

            DB::table('jurusan')->updateOrInsert(
                ['id' => $id],
                [
                    'nama_jurusan' => $nama,
                    'kode_jurusan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

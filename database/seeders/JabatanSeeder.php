<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now(tz: 'Asia/Jakarta');
        $jabatans = [
            1 => ['nama_jabatan' => 'Kepala Departemen', 'slug' => 'kadep'],
            2 => ['nama_jabatan' => 'Kepala Program Studi', 'slug' => 'kaprodi'],
            3 => ['nama_jabatan' => 'Kepala Laboratorium', 'slug' => 'kalab'],
            4 => ['nama_jabatan' => 'PLP', 'slug' => 'plp'],
            5 => ['nama_jabatan' => 'Satpam', 'slug' => 'satpam'],
        ];

        foreach ($jabatans as $id => $item) {
            $existing = DB::table('jabatans')->where('slug', $item['slug'])->first();

            if ($existing) {
                $updates = [
                    'nama_jabatan' => $item['nama_jabatan'],
                    'updated_at' => $now,
                ];

                if (
                    (int) $existing->id !== $id
                    && DB::table('users')->where('jabatan_id', $existing->id)->doesntExist()
                    && DB::table('jabatans')->where('id', $id)->doesntExist()
                ) {
                    $updates['id'] = $id;
                }

                DB::table('jabatans')->where('id', $existing->id)->update($updates);
                continue;
            }

            if (DB::table('jabatans')->where('id', $id)->exists()) {
                DB::table('jabatans')->where('id', $id)->update([
                    'nama_jabatan' => $item['nama_jabatan'],
                    'slug' => $item['slug'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('jabatans')->insert([
                    'id' => $id,
                    'nama_jabatan' => $item['nama_jabatan'],
                    'slug' => $item['slug'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now(tz: 'Asia/Jakarta');
        $roles = [
            1 => ['nama_role' => 'Admin', 'slug' => 'admin'],
            2 => ['nama_role' => 'Dosen', 'slug' => 'dosen'],
            3 => ['nama_role' => 'PLP', 'slug' => 'plp'],
            4 => ['nama_role' => 'Satpam', 'slug' => 'satpam'],
            5 => ['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa'],
        ];

        foreach ($roles as $id => $item) {
            $existing = DB::table('roles')->where('slug', $item['slug'])->first();

            if ($existing) {
                $updates = [
                    'nama_role' => $item['nama_role'],
                    'updated_at' => $now,
                ];

                if (
                    (int) $existing->id !== $id
                    && DB::table('users')->where('role_id', $existing->id)->doesntExist()
                    && DB::table('roles')->where('id', $id)->doesntExist()
                ) {
                    $updates['id'] = $id;
                }

                DB::table('roles')->where('id', $existing->id)->update($updates);
                continue;
            }

            if (DB::table('roles')->where('id', $id)->exists()) {
                DB::table('roles')->where('id', $id)->update([
                    'nama_role' => $item['nama_role'],
                    'slug' => $item['slug'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('roles')->insert([
                    'id' => $id,
                    'nama_role' => $item['nama_role'],
                    'slug' => $item['slug'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}

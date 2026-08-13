<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'nama_role' => 'Admin',
            'slug' => 'admin',
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        Role::create([
            'nama_role' => 'Dosen',
            'slug' => 'dosen',
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        Role::create([
            'nama_role' => 'PLP',
            'slug' => 'plp',
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        Role::create([
            'nama_role' => 'Satpam',
            'slug' => 'satpam',
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        Role::create([
            'nama_role' => 'Mahasiswa',
            'slug' => 'mahasiswa',
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
    }
}

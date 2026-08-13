<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'kode_prodi' => 'TI',
            'jurusan_id' => 1,
        ]);
        Prodi::create([
            'nama_prodi' => 'Multimedia Broadcasting',
            'kode_prodi' => 'MMB',
            'jurusan_id' => 2,
        ]);
    }
}

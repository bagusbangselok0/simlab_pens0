<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jabatan::create([
            'nama_jabatan' => 'Kepala Departemen',
            'slug' => 'kadep',
        ]);
        Jabatan::create([
            'nama_jabatan' => 'Kepala Program Studi',
            'slug' => 'kaprodi',
        ]);
        Jabatan::create([
            'nama_jabatan' => 'Kepala Laboratorium',
            'slug' => 'kalab',
        ]);
        Jabatan::create([
            'nama_jabatan' => 'PLP',
            'slug' => 'plp',
        ]);
        Jabatan::create([
            'nama_jabatan' => 'Satpam',
            'slug' => 'satpam',
        ]);
    }
}

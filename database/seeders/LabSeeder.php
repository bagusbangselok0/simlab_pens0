<?php

namespace Database\Seeders;

use App\Models\Lab;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lab::create([
            'nama_lab' => 'Lab Editing Video',
            'kode_lab' => 'L-201',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);
        Lab::create([
            'nama_lab' => 'Lab Pemrograman',
            'kode_lab' => 'L-202',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);
        Lab::create([
            'nama_lab' => 'Lab Database',
            'kode_lab' => 'L-203',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);
        Lab::create([
            'nama_lab' => 'Lab Jaringan',
            'kode_lab' => 'L-204',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);
            Lab::create([
                'nama_lab' => 'Studio Multimedia',
                'kode_lab' => 'K-101',
                'lokasi' => 'PSDKU Sumenep, Lantai 1',
            ]);
    }
}

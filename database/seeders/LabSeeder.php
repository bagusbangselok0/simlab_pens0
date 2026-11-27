<?php

namespace Database\Seeders;

use App\Models\Lab;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labs = [
            [
                'nama_lab' => 'Lab Editing Video',
                'kode_lab' => 'L-201',
                'lokasi' => 'PSDKU Sumenep, Lantai 2',
            ],
            [
                'nama_lab' => 'Lab Pemrograman',
                'kode_lab' => 'L-202',
                'lokasi' => 'PSDKU Sumenep, Lantai 2',
            ],
            [
                'nama_lab' => 'Lab Database',
                'kode_lab' => 'L-203',
                'lokasi' => 'PSDKU Sumenep, Lantai 2',
            ],
            [
                'nama_lab' => 'Lab Jaringan',
                'kode_lab' => 'L-204',
                'lokasi' => 'PSDKU Sumenep, Lantai 2',
            ],
            [
                'nama_lab' => 'Studio Multimedia',
                'kode_lab' => 'K-101',
                'lokasi' => 'PSDKU Sumenep, Lantai 1',
            ],
        ];

        foreach ($labs as $lab) {
            Lab::updateOrCreate(
                ['kode_lab' => $lab['kode_lab']],
                [
                    'nama_lab' => $lab['nama_lab'],
                    'lokasi' => $lab['lokasi'],
                ]
            );
        }
    }
}

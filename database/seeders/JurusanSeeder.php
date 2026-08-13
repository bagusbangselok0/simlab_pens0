<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jurusan::create([
            'nama_jurusan' => 'Departemen Teknik Informatika dan Komputer',
            'kode_jurusan' => null,
        ]);
        Jurusan::create([
            'nama_jurusan' => 'Departemen Teknologi Multimedia Kreatif',
            'kode_jurusan' => null,
        ]);
        Jurusan::create([
            'nama_jurusan' => 'Departemen Teknik Elektro',
            'kode_jurusan' => null,
        ]);
        Jurusan::create([
            'nama_jurusan' => 'Departemen Teknik Mekanika dan Energi',
            'kode_jurusan' => null,
        ]);
    }
}

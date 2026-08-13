<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nama_asli' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 1, // ID role Admin
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => null,
            'jabatan_id' => null,
            'prodi_id' => null,
            'no_hp' => null,
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Dosen Contoh',
            'email' => 'dosen@gmail.com',
            'password' => Hash::make('dosen123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'S.Kom, M.Kom.',
            'nip' => '123456789',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Deny Fardiansyah Putra',
            'email' => 'deny@pens.ac.id',
            'password' => Hash::make('deny123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'M.I.Kom.',
            'nip' => '198707062024211001',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 2,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Lusiana Agustien',
            'email' => 'lusiana@pens.ac.id',
            'password' => Hash::make('lusiana123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'S.Kom., M.Kom.',
            'nip' => '198808282023212061',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Ahmad Walid Hujairi',
            'email' => 'walid@pens.ac.id',
            'password' => Hash::make('ahmad123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'S.I.Kom., M.I.Kom.',
            'nip' => '198609262020121001',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 2,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Khoironi',
            'email' => 'khoironi@pens.ac.id',
            'password' => Hash::make('khoironi123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'S.Kom., M.Kom.',
            'nip' => '199603072022031007',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Joko Prasetyo',
            'email' => 'joko@pens.ac.id',
            'password' => Hash::make('joko123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 2, // ID role Dosen
            'gelar_depan' => null,
            'gelar_belakang' => 'S.ST., M.Kom.',
            'nip' => '198701062022031002',
            'nrp' => null,
            'jabatan_id' => 3, // ID Kalab
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'PLP Contoh',
            'email' => 'plp@gmail.com',
            'password' => Hash::make('plp123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 3, // ID role PLP
            'gelar_depan' => null,
            'gelar_belakang' => 'S.Kom.',
            'nip' => '987654321',
            'nrp' => null,
            'jabatan_id' => 4, // ID PLP
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Bagus Edi Fathorrasi',
            'email' => 'bagus_ef@staff.pens.ac.id',
            'password' => Hash::make('bagus123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 3, // ID role PLP
            'gelar_depan' => null,
            'gelar_belakang' => 'A.Md.Kom.',
            'nip' => '200203302025061005',
            'nrp' => null,
            'jabatan_id' => 4, // ID PLP
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Satpam Contoh',
            'email' => 'satpam@gmail.com',
            'password' => Hash::make('satpam123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 4, // ID role Satpam
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => '1122334455',
            'nrp' => null,
            'jabatan_id' => 5, // ID Satpam
            'prodi_id' => null,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Satpam1',
            'email' => 'satpam1@gmail.com',
            'password' => Hash::make('satpam123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 4, // ID role Satpam
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => '9988776655',
            'nrp' => null,
            'jabatan_id' => 5, // ID Satpam
            'prodi_id' => null,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Satpam2',
            'email' => 'satpam2@gmail.com',
            'password' => Hash::make('satpam123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 4, // ID role Satpam
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => '5566778899',
            'nrp' => null,
            'jabatan_id' => 5, // ID Satpam
            'prodi_id' => null,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Satpam3',
            'email' => 'satpam3@gmail.com',
            'password' => Hash::make('satpam123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 4, // ID role Satpam
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => '6677889900',
            'nrp' => null,
            'jabatan_id' => 5, // ID Satpam
            'prodi_id' => null,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Mahasiswa Contoh',
            'email' => 'mahasiswa@gmail.com',
            'password' => Hash::make('mahasiswa123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 5, // ID role Mahasiswa
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => '123456789',
            'jabatan_id' => null,
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Mahasiswa1',
            'email' => 'mahasiswa1@gmail.com',
            'password' => Hash::make('mahasiswa123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 5, // ID role Mahasiswa
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => '987654321',
            'jabatan_id' => null,
            'prodi_id' => 2,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Mahasiswa2',
            'email' => 'mahasiswa2@gmail.com',
            'password' => Hash::make('mahasiswa123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 5, // ID role Mahasiswa
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => '192837465',
            'jabatan_id' => null,
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Mahasiswa3',
            'email' => 'mahasiswa3@gmail.com',
            'password' => Hash::make('mahasiswa123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 5, // ID role Mahasiswa
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => '192439465',
            'jabatan_id' => null,
            'prodi_id' => 2,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
        User::create([
            'nama_asli' => 'Mahasiswa4',
            'email' => 'mahasiswa4@gmail.com',
            'password' => Hash::make('mahasiswa123'), // Selalu gunakan Hash untuk keamanan
            'role_id' => 5, // ID role Mahasiswa
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'nip' => null,
            'nrp' => '197430465',
            'jabatan_id' => null,
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'photo' => null,
            'signature_path' => null,
            'is_verified' => true,
            'is_active' => true,
            'created_at' => now(
                tz: 'Asia/Jakarta',
            ),
            'updated_at' => now(
                tz: 'Asia/Jakarta',
            ),
        ]);
    }
}

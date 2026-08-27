<?php

namespace Tests\Feature;

use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PresensiNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function setupRelationalData()
    {
        $roleIdMahasiswa = DB::table('roles')->insertGetId([
            'nama_role' => 'Mahasiswa',
            'slug' => 'mahasiswa',
        ]);
        $roleIdSatpam = DB::table('roles')->insertGetId([
            'nama_role' => 'Satpam',
            'slug' => 'satpam',
        ]);

        $mahasiswa = User::create([
            'role_id' => $roleIdMahasiswa,
            'nama_asli' => 'Mahasiswa Test',
            'email' => 'mhs@example.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $satpam = User::create([
            'role_id' => $roleIdSatpam,
            'nama_asli' => 'Satpam Test',
            'email' => 'satpam@example.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $labId = DB::table('labs')->insertGetId([
            'nama_lab' => 'Lab Komputer Test',
            'kode_lab' => 'LAB-KOM-' . uniqid(),
            'lokasi' => 'Lantai 1',
        ]);

        $roleIdPlp = DB::table('roles')->insertGetId([
            'nama_role' => 'PLP',
            'slug' => 'plp',
        ]);
        $plp = User::create([
            'role_id' => $roleIdPlp,
            'nama_asli' => 'PLP User',
            'email' => 'plp_notif@example.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);
        $managerId = DB::table('lab_managers')->insertGetId([
            'lab_id' => $labId,
            'plp_id' => $plp->id,
            'kalab_id' => $plp->id,
        ]);

        return [
            'mahasiswa' => $mahasiswa,
            'satpam' => $satpam,
            'lab_id' => $labId,
            'manager_id' => $managerId,
        ];
    }

    public function test_it_creates_notification_for_satpam_when_presensi_is_submitted()
    {
        $data = $this->setupRelationalData();

        $waktuMulai = Carbon::today()->setTime(8, 0, 0);
        $waktuSelesai = Carbon::today()->addDays(1)->setTime(17, 0, 0);

        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id' => $data['mahasiswa']->id,
            'lab_id' => $data['lab_id'],
            'lab_manager_id' => $data['manager_id'],
            'tujuan' => 'Test Presensi Notification',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status' => 'disetujui',
        ]);

        $this->actingAs($data['mahasiswa']);

        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));
        $response = $this->post(route('presensi.store'), [
            'peminjaman_id' => $peminjaman->id,
            'tipe_presensi' => 'masuk',
            'satpam_id' => $data['satpam']->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $data['satpam']->id,
            'notifiable_type' => User::class,
        ]);
    }
}

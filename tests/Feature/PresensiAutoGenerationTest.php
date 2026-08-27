<?php

namespace Tests\Feature;

use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\CarbonPeriod;

class PresensiAutoGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function setupRelationalData()
    {
        // 1. Buat Role
        $roleIdMahasiswa = DB::table('roles')->insertGetId([
            'nama_role' => 'Mahasiswa',
            'slug' => 'mahasiswa',
        ]);
        $roleIdPlp = DB::table('roles')->insertGetId([
            'nama_role' => 'PLP',
            'slug' => 'plp',
        ]);
        $roleIdKalab = DB::table('roles')->insertGetId([
            'nama_role' => 'Kalab',
            'slug' => 'dosen',
        ]);
        $roleIdSatpam = DB::table('roles')->insertGetId([
            'nama_role' => 'Satpam',
            'slug' => 'satpam',
        ]);

        // 2. Buat User Mahasiswa, PLP, KALAB, Satpam
        $mahasiswa = User::create([
            'role_id' => $roleIdMahasiswa,
            'nama_asli' => 'Mahasiswa Test',
            'email' => 'mhs@example.com',
            'password' => bcrypt('password'),
            'signature_path' => 'mhs_sign.png',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $plp = User::create([
            'role_id' => $roleIdPlp,
            'nama_asli' => 'PLP Test',
            'email' => 'plp@example.com',
            'password' => bcrypt('password'),
            'signature_path' => 'plp_sign.png',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $kalab = User::create([
            'role_id' => $roleIdKalab,
            'nama_asli' => 'Kalab Test',
            'email' => 'kalab@example.com',
            'password' => bcrypt('password'),
            'signature_path' => 'kalab_sign.png',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $satpam = User::create([
            'role_id' => $roleIdSatpam,
            'nama_asli' => 'Satpam Test',
            'email' => 'satpam@example.com',
            'password' => bcrypt('password'),
            'signature_path' => 'satpam_sign.png',
            'is_verified' => true,
            'is_active' => true,
        ]);

        // 3. Buat Lab
        $labId = DB::table('labs')->insertGetId([
            'nama_lab' => 'Lab Komputer Test',
            'kode_lab' => 'LAB-KOM-' . uniqid(),
            'lokasi' => 'Lantai 1'
        ]);

        // 4. Buat Lab Manager
        $managerId = DB::table('lab_managers')->insertGetId([
            'lab_id' => $labId,
            'plp_id' => $plp->id,
            'kalab_id' => $kalab->id,
        ]);

        return [
            'mahasiswa' => $mahasiswa,
            'plp' => $plp,
            'kalab' => $kalab,
            'satpam' => $satpam,
            'lab_id' => $labId,
            'manager_id' => $managerId
        ];
    }

    public function test_it_creates_presensi_records_on_final_approval()
    {
        $data = $this->setupRelationalData();

        $waktuMulai = Carbon::today()->addDays(1)->setTime(8, 0, 0);
        $waktuSelesai = Carbon::today()->addDays(3)->setTime(17, 0, 0); // 3 hari: besok, besok lusa, 3 hari lagi

        // Create a request with pending_kalab (PLP already approved)
        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id' => $data['mahasiswa']->id,
            'lab_id' => $data['lab_id'],
            'lab_manager_id' => $data['manager_id'],
            'tujuan' => 'Test Auto Presensi',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status' => 'pending_kalab',
            'tgl_ttd_plp' => Carbon::now(),
            'ttd_plp_file' => 'plp_sign.png',
        ]);

        $this->actingAs($data['kalab']);

        // Approve the request
        $response = $this->patch(route('approval.approve', $peminjaman->id));
        $response->assertStatus(200);

        // Verify Peminjaman status is 'disetujui'
        $this->assertEquals('disetujui', $peminjaman->fresh()->status);

        // Verify 3 PresensiLab records are created
        $this->assertDatabaseCount('presensi_lab', 3);

        // Verify the dates
        $this->assertDatabaseHas('presensi_lab', [
            'peminjaman_lab_id' => $peminjaman->id,
            'tanggal_presensi' => Carbon::today()->addDays(1)->toDateString(),
            'status_presensi' => 'belum_hadir',
        ]);
        $this->assertDatabaseHas('presensi_lab', [
            'peminjaman_lab_id' => $peminjaman->id,
            'tanggal_presensi' => Carbon::today()->addDays(2)->toDateString(),
            'status_presensi' => 'belum_hadir',
        ]);
        $this->assertDatabaseHas('presensi_lab', [
            'peminjaman_lab_id' => $peminjaman->id,
            'tanggal_presensi' => Carbon::today()->addDays(3)->toDateString(),
            'status_presensi' => 'belum_hadir',
        ]);
    }

    public function test_it_updates_existing_presensi_record_on_checkin()
    {
        $data = $this->setupRelationalData();

        $waktuMulai = Carbon::today()->setTime(8, 0, 0);
        $waktuSelesai = Carbon::today()->addDays(2)->setTime(17, 0, 0); // 3 hari: hari ini, besok, besok lusa

        // Create an approved request
        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id' => $data['mahasiswa']->id,
            'lab_id' => $data['lab_id'],
            'lab_manager_id' => $data['manager_id'],
            'tujuan' => 'Test Checkin Update',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status' => 'disetujui',
        ]);

        // Manually create the 3 presensi records for the period (simulating the auto-generation)
        $period = CarbonPeriod::create($waktuMulai->toDateString(), $waktuSelesai->toDateString());
        foreach ($period as $date) {
            PresensiLab::create([
                'peminjaman_lab_id' => $peminjaman->id,
                'mahasiswa_id' => $data['mahasiswa']->id,
                'tanggal_presensi' => $date->toDateString(),
                'status_presensi' => 'belum_hadir',
            ]);
        }

        $this->actingAs($data['mahasiswa']);

        // Post check-in for today
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));
        $response = $this->post(route('presensi.store'), [
            'peminjaman_id' => $peminjaman->id,
            'tipe_presensi' => 'masuk',
            'satpam_id' => $data['satpam']->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Presensi masuk berhasil diajukan. Menunggu konfirmasi dari satpam.');

        // Verify the today's record is updated instead of creating a new one (total count remains 3)
        $this->assertDatabaseCount('presensi_lab', 3);

        $this->assertDatabaseHas('presensi_lab', [
            'peminjaman_lab_id' => $peminjaman->id,
            'tanggal_presensi' => Carbon::today()->toDateString(),
            'status_presensi' => 'menunggu_konfirmasi_masuk',
            'satpam_masuk_id' => $data['satpam']->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\PeminjamanLab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpirePeminjamanLabTest extends TestCase
{
    use RefreshDatabase;

    private function setupRelationalData()
    {
        // 1. Buat Role
        $roleId = DB::table('roles')->insertGetId([
            'nama_role' => 'Mahasiswa Test',
            'slug' => 'mahasiswa-test-' . uniqid(),
        ]);

        // 2. Buat User Mahasiswa, PLP, KALAB
        $userId1 = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'nama_asli' => 'Mahasiswa 1',
            'email' => 'mhs1@example.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $userId2 = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'nama_asli' => 'Mahasiswa 2',
            'email' => 'mhs2@example.com',
            'password' => bcrypt('password'),
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
            'plp_id' => $userId1, // reuse user id as plp for testing
            'kalab_id' => $userId2, // reuse user id as kalab for testing
        ]);

        return [
            'user1' => $userId1,
            'user2' => $userId2,
            'lab' => $labId,
            'manager' => $managerId
        ];
    }

    public function test_it_cancels_unapproved_requests_older_than_24_hours()
    {
        $data = $this->setupRelationalData();

        // 1. Create a request that is 25 hours old (should be canceled)
        $expiredRequest = PeminjamanLab::create([
            'mahasiswa_id' => $data['user1'],
            'lab_id' => $data['lab'],
            'lab_manager_id' => $data['manager'],
            'tujuan' => 'Test expire schedule',
            'waktu_mulai' => Carbon::now()->addDays(1),
            'waktu_selesai' => Carbon::now()->addDays(1)->addHours(2),
            'status' => 'pending_plp',
        ]);
        $expiredRequest->created_at = Carbon::now()->subHours(25);
        $expiredRequest->save(['timestamps' => false]);

        // 2. Create a request that is 23 hours old (should NOT be canceled)
        $validRequest = PeminjamanLab::create([
            'mahasiswa_id' => $data['user2'],
            'lab_id' => $data['lab'],
            'lab_manager_id' => $data['manager'],
            'tujuan' => 'Test valid schedule',
            'waktu_mulai' => Carbon::now()->addDays(1),
            'waktu_selesai' => Carbon::now()->addDays(1)->addHours(2),
            'status' => 'pending_kalab',
        ]);
        $validRequest->created_at = Carbon::now()->subHours(23);
        $validRequest->save(['timestamps' => false]);

        // Run the console command
        $this->artisan('peminjaman:expire')->assertExitCode(0);

        // Assert the older request was canceled
        $this->assertDatabaseHas('peminjaman_lab', [
            'id' => $expiredRequest->id,
            'status' => 'dibatalkan',
        ]);

        // Assert the newer request is unchanged
        $this->assertDatabaseHas('peminjaman_lab', [
            'id' => $validRequest->id,
            'status' => 'pending_kalab',
        ]);
    }

    public function test_it_expires_approved_requests_past_end_time()
    {
        $data = $this->setupRelationalData();

        // Create an approved request that is past its end time
        $pastRequest = PeminjamanLab::create([
            'mahasiswa_id' => $data['user1'],
            'lab_id' => $data['lab'],
            'lab_manager_id' => $data['manager'],
            'tujuan' => 'Test past schedule',
            'waktu_mulai' => Carbon::now()->subDays(2),
            'waktu_selesai' => Carbon::now()->subDays(1),
            'status' => 'disetujui',
        ]);

        $futureRequest = PeminjamanLab::create([
            'mahasiswa_id' => $data['user2'],
            'lab_id' => $data['lab'],
            'lab_manager_id' => $data['manager'],
            'tujuan' => 'Test future schedule',
            'waktu_mulai' => Carbon::now()->addDays(1),
            'waktu_selesai' => Carbon::now()->addDays(2),
            'status' => 'disetujui',
        ]);

        $this->artisan('peminjaman:expire')->assertExitCode(0);

        // Past request should be marked as kadaluarsa
        $this->assertDatabaseHas('peminjaman_lab', [
            'id' => $pastRequest->id,
            'status' => 'kadaluarsa',
        ]);

        // Future request should remain disetujui
        $this->assertDatabaseHas('peminjaman_lab', [
            'id' => $futureRequest->id,
            'status' => 'disetujui',
        ]);
    }
}

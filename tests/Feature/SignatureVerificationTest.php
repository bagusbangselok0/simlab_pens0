<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\LabManager;
use App\Models\PeminjamanLab;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SignatureVerificationTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $plp;
    private $kalab;
    private $mahasiswa;
    private $lab;
    private $labManager;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['id' => 1, 'nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['id' => 2, 'nama_role' => 'PLP', 'slug' => 'plp']);
        $roleKalab = DB::table('roles')->insertGetId(['id' => 3, 'nama_role' => 'Kalab', 'slug' => 'dosen']);
        $roleMahasiswa = DB::table('roles')->insertGetId(['id' => 4, 'nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        // Pastikan jabatan ID sesuai dengan logic di controller (3 = Kalab, 4 = PLP)
        DB::table('jabatans')->insert(['id' => 3, 'nama_jabatan' => 'Kepala Lab', 'slug' => 'kalab']);
        DB::table('jabatans')->insert(['id' => 4, 'nama_jabatan' => 'PLP', 'slug' => 'plp']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'jabatan_id' => 4,
            'nama_asli' => 'PLP User',
            'nip' => '198501012010121001',
            'email' => 'plp@test.com',
            'password' => bcrypt('password'),
            'signature_path' => 'plp_sign.png',
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->kalab = User::create([
            'role_id' => $roleKalab,
            'jabatan_id' => 3,
            'nama_asli' => 'Kalab User',
            'nip' => '197501012000121002',
            'email' => 'kalab@test.com',
            'password' => bcrypt('password'),
            'signature_path' => 'kalab_sign.png',
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMahasiswa,
            'nama_asli' => 'Mhs User',
            'nrp' => '3120500001',
            'email' => 'mhs@test.com',
            'password' => bcrypt('password'),
            'signature_path' => null,
            'signature_status' => 'none',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab Multimedia',
            'kode_lab' => 'L-MM-01',
            'lokasi' => 'Gedung C Lantai 2',
        ]);

        $this->labManager = LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'kalab_id' => $this->kalab->id,
        ]);
    }

    public function test_user_upload_signature_sets_status_to_pending(): void
    {
        // Gunakan dummy binary image content agar tidak bergantung pada GD extension
        $dummyImageContent = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15c4\x00\x00\x00\nIDATx\x9cc\x00\x01\x00\x00\x05\x00\x01\r\n-\xb4\x00\x00\x00\x00IEND\xaeB`\x82";
        $file = UploadedFile::fake()->createWithContent('signature.png', $dummyImageContent);

        $response = $this->actingAs($this->mahasiswa)->post(route('profile.upload_ttd', $this->mahasiswa->id), [
            'signature_path' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->mahasiswa->refresh();
        $this->assertEquals('pending', $this->mahasiswa->signature_status);
        $this->assertNotNull($this->mahasiswa->signature_path);
    }

    public function test_admin_can_view_signature_verification_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.signatures.index'));
        $response->assertStatus(200);
        $response->assertSee('Verifikasi Tanda Tangan Digital');
    }

    public function test_pending_signature_is_returned_to_admin_datatable(): void
    {
        $this->mahasiswa->update([
            'signature_path' => 'pending_signature.png',
            'signature_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.signatures.index', [
                'status' => 'pending',
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]));

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_asli' => 'Mhs User']);
    }

    public function test_admin_can_approve_signature(): void
    {
        $signatureDir = public_path('storage/signatures');
        if (!file_exists($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }
        $dummyFile = 'test_signature_' . time() . '.png';
        file_put_contents($signatureDir . '/' . $dummyFile, 'dummy content');

        $this->mahasiswa->update([
            'signature_path' => $dummyFile,
            'signature_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/verifikasi-ttd/{$this->mahasiswa->id}/approve");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->mahasiswa->refresh();
        $this->assertEquals('approved', $this->mahasiswa->signature_status);
        $this->assertNotNull($this->mahasiswa->signature_verified_at);
        $this->assertEquals($this->admin->id, $this->mahasiswa->signature_verified_by);

        if (file_exists($signatureDir . '/' . $dummyFile)) {
            unlink($signatureDir . '/' . $dummyFile);
        }
    }

    public function test_admin_can_reject_signature_and_file_is_deleted(): void
    {
        $signatureDir = public_path('storage/signatures');
        if (!file_exists($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }
        $dummyFile = 'test_reject_' . time() . '.png';
        $fullPath = $signatureDir . '/' . $dummyFile;
        file_put_contents($fullPath, 'dummy content');

        $this->mahasiswa->update([
            'signature_path' => $dummyFile,
            'signature_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/verifikasi-ttd/{$this->mahasiswa->id}/reject", [
            'rejection_note' => 'Gambar buram dan background tidak putih',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->mahasiswa->refresh();
        $this->assertEquals('rejected', $this->mahasiswa->signature_status);
        $this->assertNull($this->mahasiswa->signature_path);
        $this->assertEquals('Gambar buram dan background tidak putih', $this->mahasiswa->signature_rejection_note);

        // Verifikasi file fisik terhapus oleh sistem
        $this->assertFalse(file_exists($fullPath));
    }

    public function test_mahasiswa_with_unapproved_or_pending_signature_cannot_submit_peminjaman(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        // 1. Status 'none'
        $response = $this->actingAs($this->mahasiswa)->post(route('peminjaman.store'), [
            'lab_id' => $this->lab->id,
            'tujuan' => 'Riset Skripsi',
            'waktu_mulai' => Carbon::now()->addDay()->setTime(8, 0, 0)->format('Y-m-d H:i:s'),
            'waktu_selesai' => Carbon::now()->addDays(2)->setTime(16, 0, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);

        // 2. Status 'pending'
        $this->mahasiswa->update([
            'signature_path' => 'mhs_pending.png',
            'signature_status' => 'pending',
        ]);

        $response2 = $this->actingAs($this->mahasiswa)->post(route('peminjaman.store'), [
            'lab_id' => $this->lab->id,
            'tujuan' => 'Riset Skripsi 2',
            'waktu_mulai' => Carbon::now()->addDay()->setTime(8, 0, 0)->format('Y-m-d H:i:s'),
            'waktu_selesai' => Carbon::now()->addDays(2)->setTime(16, 0, 0)->format('Y-m-d H:i:s'),
        ]);

        $response2->assertStatus(422);
        $response2->assertJsonFragment(['success' => false]);
    }

    public function test_mahasiswa_with_approved_signature_can_submit_peminjaman(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        $this->mahasiswa->update([
            'signature_path' => 'mhs_approved.png',
            'signature_status' => 'approved',
        ]);

        $response = $this->actingAs($this->mahasiswa)->post(route('peminjaman.store'), [
            'lab_id' => $this->lab->id,
            'tujuan' => 'Riset Skripsi Approved',
            'waktu_mulai' => Carbon::now()->addDay()->setTime(8, 0, 0)->format('Y-m-d H:i:s'),
            'waktu_selesai' => Carbon::now()->addDays(2)->setTime(16, 0, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('peminjaman_lab', [
            'mahasiswa_id' => $this->mahasiswa->id,
            'tujuan' => 'Riset Skripsi Approved',
        ]);
    }

    public function test_plp_with_unapproved_signature_cannot_approve_peminjaman(): void
    {
        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'lab_id' => $this->lab->id,
            'lab_manager_id' => $this->labManager->id,
            'tujuan' => 'Peminjaman Uji Coba',
            'waktu_mulai' => Carbon::now()->addDay()->setTime(8, 0, 0),
            'waktu_selesai' => Carbon::now()->addDays(2)->setTime(16, 0, 0),
            'ttd_mahasiswa_file' => 'sign.png',
            'status' => 'pending_plp',
        ]);

        $this->plp->update([
            'signature_status' => 'pending',
        ]);

        $response = $this->actingAs($this->plp)->patch(route('approval.approve', $peminjaman->id));
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_plp_with_approved_signature_can_approve_peminjaman(): void
    {
        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'lab_id' => $this->lab->id,
            'lab_manager_id' => $this->labManager->id,
            'tujuan' => 'Peminjaman Uji Coba Berhasil',
            'waktu_mulai' => Carbon::now()->addDay()->setTime(8, 0, 0),
            'waktu_selesai' => Carbon::now()->addDays(2)->setTime(16, 0, 0),
            'ttd_mahasiswa_file' => 'sign.png',
            'status' => 'pending_plp',
        ]);

        $this->plp->update([
            'signature_path' => 'plp_sign.png',
            'signature_status' => 'approved',
        ]);

        $response = $this->actingAs($this->plp)->patch(route('approval.approve', $peminjaman->id));
        $response->assertStatus(200);

        $peminjaman->refresh();
        $this->assertEquals('pending_kalab', $peminjaman->status);
    }
}

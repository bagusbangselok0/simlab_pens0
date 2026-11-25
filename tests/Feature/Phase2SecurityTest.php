<?php

namespace Tests\Feature;

use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\PeminjamanAlat;
use App\Models\PeminjamanAlatDetail;
use App\Models\PeminjamanLab;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class Phase2SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plpA;
    private User $plpB;
    private User $kalabA;
    private User $kalabB;
    private User $dosenNonKalab;
    private User $mhsA;
    private User $mhsB;
    private Lab $labA;
    private Lab $labB;
    private LabManager $managerA;
    private LabManager $managerB;
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $roleMhs = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $jabKalab = DB::table('jabatans')->insertGetId(['id' => 3, 'nama_jabatan' => 'Kepala Laboratorium', 'slug' => 'kalab']);
        $jabPlp = DB::table('jabatans')->insertGetId(['id' => 4, 'nama_jabatan' => 'PLP', 'slug' => 'plp']);

        $sigFile = $this->createPrivateSig('phase2_sig_' . uniqid() . '.png');

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Phase2',
            'email' => 'admin-p2@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plpA = User::create([
            'role_id' => $rolePlp,
            'jabatan_id' => $jabPlp,
            'nama_asli' => 'PLP Lab A',
            'email' => 'plpa-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plpB = User::create([
            'role_id' => $rolePlp,
            'jabatan_id' => $jabPlp,
            'nama_asli' => 'PLP Lab B',
            'email' => 'plpb-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->kalabA = User::create([
            'role_id' => $roleDosen,
            'jabatan_id' => $jabKalab,
            'nama_asli' => 'Kalab Lab A',
            'email' => 'kalaba-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->kalabB = User::create([
            'role_id' => $roleDosen,
            'jabatan_id' => $jabKalab,
            'nama_asli' => 'Kalab Lab B',
            'email' => 'kalabb-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosenNonKalab = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Dosen Non Kalab',
            'email' => 'dosennk-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mhsA = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => 'Mahasiswa A',
            'nrp' => '3125500101',
            'email' => 'mhsa-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mhsB = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => 'Mahasiswa B',
            'nrp' => '3125500102',
            'email' => 'mhsb-p2@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->labA = Lab::create([
            'nama_lab' => 'Lab A',
            'kode_lab' => 'L-P2-A',
            'lokasi' => 'Gedung A',
        ]);

        $this->labB = Lab::create([
            'nama_lab' => 'Lab B',
            'kode_lab' => 'L-P2-B',
            'lokasi' => 'Gedung B',
        ]);

        $this->managerA = LabManager::create([
            'lab_id' => $this->labA->id,
            'plp_id' => $this->plpA->id,
            'kalab_id' => $this->kalabA->id,
        ]);

        $this->managerB = LabManager::create([
            'lab_id' => $this->labB->id,
            'plp_id' => $this->plpB->id,
            'kalab_id' => $this->kalabB->id,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanupPaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        parent::tearDown();
    }

    private function createPrivateSig(string $filename): string
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        Storage::disk('local')->put('signatures/' . $filename, $pngBinary);
        $this->cleanupPaths[] = Storage::disk('local')->path('signatures/' . $filename);

        return $filename;
    }

    public function test_anti_idor_and_policy_enforced_across_peminjaman_dir_and_logbook(): void
    {
        // 1. Peminjaman Lab A milik Mhs A
        $pendingLabA = PeminjamanLab::create([
            'mahasiswa_id' => $this->mhsA->id,
            'lab_id' => $this->labA->id,
            'lab_manager_id' => $this->managerA->id,
            'tujuan' => 'Praktikum Lab A',
            'waktu_mulai' => Carbon::now()->addDay(),
            'waktu_selesai' => Carbon::now()->addDays(2),
            'ttd_mahasiswa_file' => $this->mhsA->signature_path,
            'status' => 'pending_plp',
        ]);

        $approvedLabA = PeminjamanLab::create([
            'mahasiswa_id' => $this->mhsA->id,
            'lab_id' => $this->labA->id,
            'lab_manager_id' => $this->managerA->id,
            'tujuan' => 'Riset Disetujui Lab A',
            'waktu_mulai' => Carbon::now()->addDays(3),
            'waktu_selesai' => Carbon::now()->addDays(4),
            'ttd_mahasiswa_file' => $this->mhsA->signature_path,
            'ttd_plp_file' => $this->plpA->signature_path,
            'ttd_kalab_file' => $this->kalabA->signature_path,
            'status' => 'disetujui',
        ]);
        $approvedLabA->tgl_ttd_plp = now();
        $approvedLabA->tgl_ttd_kalab = now();
        $approvedLabA->save();

        // Mahasiswa B tidak boleh melihat detail, cancel, atau cetak PDF milik Mahasiswa A
        $this->actingAs($this->mhsB)->get(route('peminjaman.detail', $approvedLabA->id))->assertStatus(403);
        $this->actingAs($this->mhsB)->patch(route('peminjaman.cancel', $pendingLabA->id))->assertStatus(403);
        $this->actingAs($this->mhsB)->get(route('peminjaman.cetak', $approvedLabA->id))->assertStatus(403);

        // Pemilik belum boleh cetak PDF bila status masih pending_plp
        $this->actingAs($this->mhsA)->get(route('peminjaman.cetak', $pendingLabA->id))->assertStatus(403);

        // PLP B, Kalab B, dan Dosen Non-Kalab tidak boleh approve/reject/cetak milik Lab A
        $this->actingAs($this->plpB)->patch(route('approval.approve', $pendingLabA->id))->assertStatus(403);
        $this->actingAs($this->plpB)->patch(route('approval.reject', $pendingLabA->id))->assertStatus(403);
        $this->actingAs($this->kalabB)->get(route('presensi.monitoring.cetak', $approvedLabA->id))->assertStatus(403);
        $this->actingAs($this->dosenNonKalab)->get(route('presensi.monitoring.cetak', $approvedLabA->id))->assertStatus(403);

        // Kalab A & Mahasiswa A diizinkan cetak PDF
        $this->actingAs($this->kalabA)->get(route('presensi.monitoring.cetak', $approvedLabA->id))->assertStatus(200);
        $this->actingAs($this->mhsA)->get(route('peminjaman.cetak', $approvedLabA->id))->assertStatus(200);

        // 2. DIR (InventarisRuangan) Lab A
        $invA = InventarisRuangan::create([
            'lab_id' => $this->labA->id,
            'kode_barang' => 'K-DIR-01',
            'nup' => '1',
            'nama_barang' => 'Router MikroTik',
            'spesifikasi_merk_tipe' => 'RB4011',
            'tahun_perolehan' => 2024,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
            'is_bisa_dipinjam' => true,
        ]);

        $this->actingAs($this->plpB)->get(route('inventaris-ruangan.index', ['lab_id' => $this->labA->id]))->assertStatus(403);
        $this->actingAs($this->plpB)->get(route('inventaris-ruangan.show', $invA->id))->assertStatus(403);
        $this->actingAs($this->plpB)->get(route('inventaris-ruangan.export-pdf', $this->labA->id))->assertStatus(403);
        $this->actingAs($this->kalabB)->get(route('inventaris-ruangan.export-pdf', $this->labA->id))->assertStatus(403);
        $this->actingAs($this->kalabA)->get(route('inventaris-ruangan.export-pdf', $this->labA->id))->assertStatus(200);

        // 3. Peminjaman Alat Lab A
        $loanAlatPending = PeminjamanAlat::create([
            'kode_peminjaman' => 'ALAT-IDOR-001',
            'mahasiswa_id' => $this->mhsA->id,
            'lab_id' => $this->labA->id,
            'plp_id' => $this->plpA->id,
            'keperluan' => 'Uji Jaringan',
            'waktu_pinjam' => Carbon::now()->addHour(),
            'rencana_kembali' => Carbon::now()->addDay(),
            'status' => 'pending_plp',
            'ttd_mahasiswa_file' => $this->mhsA->signature_path,
        ]);

        PeminjamanAlatDetail::create([
            'peminjaman_alat_id' => $loanAlatPending->id,
            'inventaris_ruangan_id' => $invA->id,
            'nama_alat' => $invA->nama_barang,
            'kode_barang' => $invA->kode_barang,
            'nup' => '1',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'is_manual' => false,
        ]);

        $this->actingAs($this->mhsB)->get(route('peminjaman-alat.show', $loanAlatPending->id))->assertStatus(403);
        $this->actingAs($this->mhsB)->patch(route('peminjaman-alat.cancel', $loanAlatPending->id))->assertStatus(403);
        $this->actingAs($this->mhsA)->get(route('peminjaman-alat.cetak', $loanAlatPending->id))->assertStatus(403);
        $this->actingAs($this->plpB)->get(route('peminjaman-alat.show', $loanAlatPending->id))->assertStatus(403);
        $this->actingAs($this->plpB)->patch(route('peminjaman-alat.approve', $loanAlatPending->id))->assertStatus(403);
        $this->actingAs($this->plpB)->patch(route('peminjaman-alat.reject', $loanAlatPending->id), ['catatan_penolakan' => 'Ditolak IDOR'])->assertStatus(403);

        // 4. Logbook Praktikum Lab A (PLP A & Dosen Pengampu non-Kalab)
        $book = LogbookPraktikum::create([
            'lab_id' => $this->labA->id,
            'plp_id' => $this->plpA->id,
            'dosen_id' => $this->dosenNonKalab->id,
            'nama_mk' => 'Jaringan Komputer',
            'kelas' => '2 D3 IT',
            'semester' => 'Ganjil',
            'tahun_akademik' => '2026/2027',
        ]);

        $pertemuan = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Routing Statis',
            'tanggal_pelaksanaan' => '2026-09-10',
            'jam_pelaksanaan' => '08:00 WIB',
            'status' => LogbookPertemuan::STATUS_PENDING_DOSEN,
        ]);

        $this->actingAs($this->plpB)->get(route('logbook.show', $book->id))->assertStatus(403);
        $this->actingAs($this->plpB)->get(route('logbook.export-pdf', $book->id))->assertStatus(403);
        $this->actingAs($this->kalabB)->get(route('logbook.export-pdf', $book->id))->assertStatus(403);
        $this->actingAs($this->kalabB)->post(route('logbook.approval.approve', $pertemuan->id))->assertStatus(403);

        // Dosen Pengampu sah boleh cetak PDF logbook miliknya
        $this->actingAs($this->dosenNonKalab)->get(route('logbook.export-pdf', $book->id))->assertStatus(200);
    }

    public function test_peminjaman_alat_prevents_double_booking_same_nup_and_allows_different_nup(): void
    {
        $inv = InventarisRuangan::create([
            'lab_id' => $this->labA->id,
            'kode_barang' => 'K-ALAT-100',
            'nup' => '1, 2',
            'nama_barang' => 'Multimeter Digital',
            'spesifikasi_merk_tipe' => 'Fluke 117',
            'tahun_perolehan' => 2025,
            'jumlah' => 2,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
            'is_bisa_dipinjam' => true,
        ]);

        $basePayload = [
            'lab_id' => $this->labA->id,
            'keperluan' => 'Praktikum Elektronika',
            'waktu_pinjam' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
            'rencana_kembali' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
        ];

        // Mahasiswa A meminjam NUP 1
        $this->actingAs($this->mhsA)->postJson(route('peminjaman-alat.store'), array_merge($basePayload, [
            'items' => [[
                'is_manual' => 0,
                'inventaris_ruangan_id' => $inv->id . '__1',
                'nup' => '1',
                'jumlah' => 1,
            ]],
        ]))->assertStatus(200)->assertJson(['success' => true]);

        // Mahasiswa B mencoba meminjam NUP 1 yang sama -> ditolak 422 oleh pessimistic check
        $this->actingAs($this->mhsB)->postJson(route('peminjaman-alat.store'), array_merge($basePayload, [
            'items' => [[
                'is_manual' => 0,
                'inventaris_ruangan_id' => $inv->id,
                'nup' => '1',
                'jumlah' => 1,
            ]],
        ]))->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseCount('peminjaman_alats', 1);

        // Mahasiswa B meminjam NUP 2 pada DIR yang sama -> diperbolehkan 200
        $this->actingAs($this->mhsB)->postJson(route('peminjaman-alat.store'), array_merge($basePayload, [
            'items' => [[
                'is_manual' => 0,
                'inventaris_ruangan_id' => $inv->id . '__2',
                'nup' => '2',
                'jumlah' => 1,
            ]],
        ]))->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseCount('peminjaman_alats', 2);
    }

    public function test_public_qr_sign_endpoint_enforces_status_lock_magic_bytes_and_rate_limit(): void
    {
        $book = LogbookPraktikum::create([
            'lab_id' => $this->labA->id,
            'plp_id' => $this->plpA->id,
            'dosen_id' => $this->dosenNonKalab->id,
            'nama_mk' => 'Workshop Keamanan',
            'kelas' => '3 D4 IT',
            'semester' => 'Genap',
            'tahun_akademik' => '2026/2027',
        ]);

        $pendingPertemuan = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Menunggu TTD Dosen',
            'tanggal_pelaksanaan' => '2026-09-12',
            'jam_pelaksanaan' => '08:00 WIB',
            'status' => LogbookPertemuan::STATUS_PENDING_DOSEN,
        ]);

        // 1. Pertemuan status pending_dosen harus ditolak pada QR link & public endpoint
        $this->actingAs($this->plpA)->postJson(route('logbook.sign-link'), [
            'target_type' => 'pertemuan',
            'target_id' => $pendingPertemuan->id,
        ])->assertStatus(422);

        $pendingShowUrl = URL::temporarySignedRoute('logbook.sign-mahasiswa.show', now()->addMinutes(10), [
            'type' => 'pertemuan',
            'id' => $pendingPertemuan->id,
        ]);
        $pendingSubmitUrl = URL::temporarySignedRoute('logbook.sign-mahasiswa.submit', now()->addMinutes(10), [
            'type' => 'pertemuan',
            'id' => $pendingPertemuan->id,
        ]);

        $validPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $this->get($pendingShowUrl)->assertStatus(403);
        $this->post($pendingSubmitUrl, [
            'mahasiswa_nama' => 'Mahasiswa Nakal',
            'mahasiswa_nrp' => '3125500999',
            'signature_data' => $validPng,
        ])->assertStatus(403);

        // 2. Pertemuan status draft: payload bukan PNG valid (magic bytes palsu) harus ditolak
        $draftPertemuan = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 2,
            'judul_praktikum' => 'Pertemuan Draft',
            'tanggal_pelaksanaan' => '2026-09-19',
            'jam_pelaksanaan' => '08:00 WIB',
            'status' => LogbookPertemuan::STATUS_DRAFT,
        ]);

        $draftShowUrl = URL::temporarySignedRoute('logbook.sign-mahasiswa.show', now()->addMinutes(10), [
            'type' => 'pertemuan',
            'id' => $draftPertemuan->id,
        ]);
        $draftSubmitUrl = URL::temporarySignedRoute('logbook.sign-mahasiswa.submit', now()->addMinutes(10), [
            'type' => 'pertemuan',
            'id' => $draftPertemuan->id,
        ]);

        $fakePngDataUri = 'data:image/png;base64,' . base64_encode(str_repeat('NOT_A_REAL_PNG_FILE_', 5));
        $this->post($draftSubmitUrl, [
            'mahasiswa_nama' => 'Mahasiswa Uji',
            'mahasiswa_nrp' => '3125500111',
            'signature_data' => $fakePngDataUri,
        ])->assertSessionHasErrors('signature_data');

        $draftPertemuan->refresh();
        $this->assertNull($draftPertemuan->mahasiswa_ttd_path);

        // 3. Rate limiter publik (maks 10 req/menit per IP) memblokir request ke-11 dengan 429
        for ($i = 1; $i <= 10; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.99.99.50'])
                ->get($draftShowUrl)
                ->assertStatus(200);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.99.99.50'])
            ->get($draftShowUrl)
            ->assertStatus(429);
    }
}

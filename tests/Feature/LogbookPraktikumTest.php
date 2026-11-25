<?php

namespace Tests\Feature;

use App\Models\DosenLogbookAutoApprove;
use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\PeriodeJadwal;
use App\Models\PlpAktivitasTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LogbookPraktikumTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $dosenPengampu;
    private User $dosenKalab;
    private User $mahasiswa;
    private Lab $lab;
    private array $createdSignatureFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleMahasiswa = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $jabatanKalab = DB::table('jabatans')->insertGetId(['nama_jabatan' => 'Kepala Laboratorium', 'slug' => 'kalab']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Logbook',
            'email' => 'admin-lb@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $plpSig = $this->createDummySignatureFile('plp_test_sig.png');
        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'Bagus Edi Fathorrasi',
            'gelar_belakang' => 'A.Md.Kom.',
            'nip' => '199501012020121001',
            'email' => 'plp-lb@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $plpSig,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $dosenSig = $this->createDummySignatureFile('dosen_test_sig.png');
        $this->dosenPengampu = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Lusiana Agustien',
            'gelar_belakang' => 'S.Kom., M.Kom.',
            'nip' => '198501012010122001',
            'email' => 'lusiana-lb@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $dosenSig,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosenKalab = User::create([
            'role_id' => $roleDosen,
            'jabatan_id' => $jabatanKalab,
            'nama_asli' => 'Nirwana Haidar Hari',
            'nip' => '198201012008121001',
            'email' => 'kalab-lb@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $dosenSig,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMahasiswa,
            'nama_asli' => 'Budi Santoso',
            'nrp' => '3125500010',
            'email' => 'mhs-lb@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab Database',
            'kode_lab' => 'L-203',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);

        LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'kalab_id' => $this->dosenKalab->id,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdSignatureFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    private function createDummySignatureFile(string $filename): string
    {
        $dir = storage_path('app/private/signatures');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, $pngBinary);
        $this->createdSignatureFiles[] = $fullPath;

        return $filename;
    }

    public function test_dosen_non_kalab_is_separated_from_kalab_features(): void
    {
        $this->assertFalse($this->dosenPengampu->isKalab());
        $this->assertTrue($this->dosenKalab->isKalab());

        // Dosen non-Kalab ditolak di halaman khusus Kalab
        $this->actingAs($this->dosenPengampu)->get(route('approval.index'))->assertStatus(403);
        $this->actingAs($this->dosenPengampu)->get(route('inventaris-ruangan.index'))->assertStatus(403);
        $this->actingAs($this->dosenPengampu)->get(route('presensi.monitoring'))->assertStatus(403);

        // Dosen non-Kalab bisa akses Dashboard Dosen Pengampu & Approval Logbook tanpa menu Kalab
        $dashNonKalab = $this->actingAs($this->dosenPengampu)->get(route('dashboard'));
        $dashNonKalab->assertStatus(200);
        $dashNonKalab->assertSee('Area Dosen Pengampu Praktikum');
        $dashNonKalab->assertSee('Approval Logbook');
        $dashNonKalab->assertDontSee('Area Kepala Laboratorium (Kalab)');
        $dashNonKalab->assertDontSee('Approval Peminjaman');

        $resp = $this->actingAs($this->dosenPengampu)->get(route('logbook.approval.index'));
        $resp->assertStatus(200);
        $resp->assertSee('Approval Logbook');
        $resp->assertDontSee('Approval Lab');
        $resp->assertDontSee('Monitoring Presensi');

        // Kalab tetap dapat mengakses fitur Kalab, dashboard Kalab, dan Approval Logbook
        $dashKalab = $this->actingAs($this->dosenKalab)->get(route('dashboard'));
        $dashKalab->assertStatus(200);
        $dashKalab->assertSee('Area Kepala Laboratorium (Kalab)');
        $this->actingAs($this->dosenKalab)->get(route('approval.index'))->assertStatus(200);
        $this->actingAs($this->dosenKalab)->get(route('inventaris-ruangan.index'))->assertStatus(200);
        $this->actingAs($this->dosenKalab)->get(route('presensi.monitoring'))->assertStatus(200);
        $this->actingAs($this->dosenKalab)->get(route('logbook.approval.index'))->assertStatus(200);
    }

    public function test_plp_can_create_logbook_from_active_jadwal_generate_meetings_and_use_templates(): void
    {
        $periode = PeriodeJadwal::create([
            'nama_periode' => 'Semester Ganjil 2026/2027',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'is_active' => true,
            'uploaded_by' => $this->admin->id,
        ]);

        $jadwalPraktek = JadwalLab::create([
            'periode_jadwal_id' => $periode->id,
            'lab_id' => $this->lab->id,
            'nama_ruangan' => 'Lab L-203',
            'hari' => 'Kamis',
            'jam_ke_mulai' => 3,
            'jam_ke_selesai' => 4,
            'waktu_mulai' => '09.40',
            'waktu_berakhir' => '11.20',
            'mata_kuliah' => 'Praktek Basis Data',
            'jenis' => 'Praktek',
            'kelas' => '1 SM D3 IT',
            'semester' => '1',
            'program_studi' => 'TI',
            'dosen_id' => $this->dosenPengampu->id,
            'nama_dosen' => 'Lusiana Agustien',
        ]);

        // 1-klik buat logbook dari jadwal aktif
        $syncResp = $this->actingAs($this->plp)->post(route('logbook.store-from-jadwal'), [
            'jadwal_lab_ids' => [$jadwalPraktek->id],
            'keterangan_plp' => 'PLP Terampil',
        ]);
        $syncResp->assertRedirect(route('logbook.index'));

        $book = LogbookPraktikum::where('jadwal_lab_id', $jadwalPraktek->id)->first();
        $this->assertNotNull($book);
        $this->assertSame('Praktek Basis Data', $book->nama_mk);
        $this->assertSame('Kamis', $book->hari);
        $this->assertSame($this->dosenPengampu->id, $book->dosen_id);

        // Generate 3 minggu selaras hari Kamis
        $genResp = $this->actingAs($this->plp)->post(route('logbook.generate-pertemuans', $book->id), [
            'tanggal_mulai' => '2026-08-17', // Senin -> otomatis selaras ke Kamis 2026-08-20
            'minggu_mulai' => 1,
            'minggu_selesai' => 3,
            'judul_prefix' => 'Praktek Basis Data',
        ]);
        $genResp->assertRedirect(route('logbook.show', $book->id));

        $pertemuans = $book->pertemuans()->orderBy('minggu_ke')->get();
        $this->assertCount(3, $pertemuans);
        $this->assertSame(Carbon::THURSDAY, $pertemuans[0]->tanggal_pelaksanaan->dayOfWeek);
        $this->assertSame('2026-08-20', $pertemuans[0]->tanggal_pelaksanaan->toDateString());
        $this->assertSame('2026-08-27', $pertemuans[1]->tanggal_pelaksanaan->toDateString());

        // Simpan template aktivitas
        $this->actingAs($this->plp)->post(route('logbook.templates.store'), [
            'nama_template' => 'Standar Lab Komputer',
            'aktivitas_text' => "Menyalakan PC lab\nMendampingi praktikum",
        ])->assertRedirect();

        $this->assertDatabaseHas('plp_aktivitas_templates', [
            'plp_id' => $this->plp->id,
            'nama_template' => 'Standar Lab Komputer',
        ]);
    }

    public function test_hybrid_mahasiswa_signature_pad_and_public_signed_url(): void
    {
        $book = LogbookPraktikum::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'dosen_id' => $this->dosenPengampu->id,
            'nama_mk' => 'Praktek Basis Data',
            'kelas' => '1 SM D3 IT',
            'hari' => 'Kamis',
            'semester' => 'Ganjil',
            'tahun_akademik' => '2026/2027',
            'jam_default' => '08.40 WIB',
        ]);

        $dummyPngDataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        // 1. TTD langsung lewat Signature Pad untuk 1 semester
        $sigResp = $this->actingAs($this->plp)->post(route('logbook.mahasiswa-signature'), [
            'target_type' => 'logbook',
            'target_id' => $book->id,
            'mahasiswa_user_id' => $this->mahasiswa->id,
            'mahasiswa_nama' => 'Budi Santoso',
            'mahasiswa_nrp' => '3125500010',
            'signature_data' => $dummyPngDataUri,
        ]);
        $sigResp->assertRedirect(route('logbook.show', $book->id));

        $book->refresh();
        $this->assertSame('Budi Santoso', $book->default_mahasiswa_nama);
        $this->assertNotNull($book->default_mahasiswa_ttd_path);
        $path1 = storage_path('app/private/logbook_signatures/' . $book->default_mahasiswa_ttd_path);
        $this->assertFileExists($path1);
        $this->createdSignatureFiles[] = $path1;

        // 2. QR / Link Signed URL tanpa login untuk override pertemuan tertentu
        $pertemuan = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Pengenalan Database',
            'tanggal_pelaksanaan' => '2026-08-21',
            'jam_pelaksanaan' => '08:40 WIB',
            'peralatan' => [
                ['nama_alat' => 'Kabel UTP', 'kategori' => 'Alat 1', 'jumlah' => '2', 'kondisi' => 'Baik'],
            ],
            'status' => LogbookPertemuan::STATUS_DRAFT,
        ]);

        $linkResp = $this->actingAs($this->plp)->postJson(route('logbook.sign-link'), [
            'target_type' => 'pertemuan',
            'target_id' => $pertemuan->id,
        ]);
        $linkResp->assertStatus(200)->assertJson(['success' => true]);

        $signedShowUrl = $linkResp->json('url');
        // Akses tanpa login (guest) harus berhasil karena signed URL valid
        Auth::logout();
        $this->get($signedShowUrl)->assertStatus(200)->assertSee('Tanda Tangan Perwakilan Mahasiswa');

        $signedSubmitUrl = URL::temporarySignedRoute(
            'logbook.sign-mahasiswa.submit',
            now()->addMinutes(10),
            ['type' => 'pertemuan', 'id' => $pertemuan->id]
        );

        $this->post($signedSubmitUrl, [
            'mahasiswa_nama' => 'Siti Aminah',
            'mahasiswa_nrp' => '3125500099',
            'signature_data' => $dummyPngDataUri,
        ])->assertStatus(200)->assertSee('Tanda Tangan Berhasil Disimpan');

        $pertemuan->refresh();
        $this->assertSame('Siti Aminah', $pertemuan->mahasiswa_nama);
        $this->assertNotNull($pertemuan->mahasiswa_ttd_path);
        $path2 = storage_path('app/private/logbook_signatures/' . $pertemuan->mahasiswa_ttd_path);
        $this->assertFileExists($path2);
        $this->createdSignatureFiles[] = $path2;
    }

    public function test_manual_and_auto_approve_workflow_revoke_and_pdf_export(): void
    {
        $book = LogbookPraktikum::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'dosen_id' => $this->dosenPengampu->id,
            'nama_mk' => 'Praktek Basis Data',
            'kelas' => '1 SM D3 IT',
            'hari' => 'Kamis',
            'semester' => 'Ganjil',
            'tahun_akademik' => '2026/2027',
            'jam_default' => '08:40 WIB',
            'keterangan_plp' => 'PLP Terampil',
        ]);

        $p1 = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Pengenalan Database',
            'tanggal_pelaksanaan' => '2026-08-21',
            'jam_pelaksanaan' => '08:40 WIB',
            'aktivitas' => ['Menyiapkan PC lab dan server SQL'],
            'status' => LogbookPertemuan::STATUS_DRAFT,
        ]);

        $p2 = LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 2,
            'judul_praktikum' => 'Normalisasi Tabel',
            'tanggal_pelaksanaan' => '2026-08-28',
            'jam_pelaksanaan' => '08:40 WIB',
            'peralatan' => [
                ['nama_alat' => 'Proyektor', 'kategori' => 'Alat 1', 'jumlah' => '1', 'kondisi' => 'Baik'],
            ],
            'aktivitas' => ['Mendampingi praktikum normalisasi'],
            'permasalahan' => [
                ['masalah' => 'Proyektor redup', 'solusi' => 'Ganti kabel HDMI'],
            ],
            'status' => LogbookPertemuan::STATUS_DRAFT,
        ]);

        // --- A. MODE MANUAL APPROVE & INTEGRITAS STATUS ---
        // PLP tidak boleh memindahkan buku ke lab lain yang bukan kelolaannya
        $otherLab = Lab::create([
            'nama_lab' => 'Lab Lain',
            'kode_lab' => 'L-999',
            'lokasi' => 'Gedung Lain',
        ]);
        $this->actingAs($this->plp)->put(route('logbook.update', $book->id), [
            'lab_id' => $otherLab->id,
            'nama_mk' => $book->nama_mk,
            'kelas' => $book->kelas,
            'semester' => $book->semester,
            'tahun_akademik' => $book->tahun_akademik,
        ])->assertStatus(403);

        $this->actingAs($this->plp)->post(route('logbook.pertemuan.submit', $p1->id))->assertRedirect();
        $p1->refresh();
        $this->assertSame(LogbookPertemuan::STATUS_PENDING_DOSEN, $p1->status);

        // Saat pending_dosen, PLP tidak boleh mengedit atau menghapus pertemuan
        $this->actingAs($this->plp)->put(route('logbook.pertemuan.update', $p1->id), [
            'minggu_ke' => 1,
            'judul_praktikum' => 'Diubah Diam-Diam',
            'tanggal_pelaksanaan' => '2026-08-21',
            'jam_pelaksanaan' => '08:40 WIB',
        ])->assertSessionHas('error');
        $this->actingAs($this->plp)->delete(route('logbook.pertemuan.destroy', $p1->id))
            ->assertSessionHas('error');
        $p1->refresh();
        $this->assertSame('Pengenalan Database', $p1->judul_praktikum);

        // Dosen tolak dengan catatan
        $this->actingAs($this->dosenPengampu)->post(route('logbook.approval.reject', $p1->id), [
            'rejection_note' => 'Tambahkan detail praktikum.',
        ])->assertRedirect();
        $p1->refresh();
        $this->assertSame(LogbookPertemuan::STATUS_REJECTED, $p1->status);
        $this->assertSame('Tambahkan detail praktikum.', $p1->rejection_note);

        // PLP ajukan ulang -> Dosen approve manual
        $this->actingAs($this->plp)->post(route('logbook.pertemuan.submit', $p1->id))->assertRedirect();
        $this->actingAs($this->dosenPengampu)->post(route('logbook.approval.approve', $p1->id))->assertRedirect();
        $p1->refresh();
        $this->assertSame(LogbookPertemuan::STATUS_APPROVED, $p1->status);
        $this->assertSame('manual', $p1->approval_mode);

        // Pertemuan yang sudah approved mengunci TTD mahasiswa (pad, QR link, dan public submit)
        $this->actingAs($this->plp)->post(route('logbook.mahasiswa-signature'), [
            'target_type' => 'pertemuan',
            'target_id' => $p1->id,
            'mahasiswa_nama' => 'Penyusup',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ])->assertSessionHas('error');
        $this->actingAs($this->plp)->postJson(route('logbook.sign-link'), [
            'target_type' => 'pertemuan',
            'target_id' => $p1->id,
        ])->assertStatus(422);
        Auth::logout();
        $lockedSubmitUrl = URL::temporarySignedRoute('logbook.sign-mahasiswa.submit', now()->addMinutes(5), ['type' => 'pertemuan', 'id' => $p1->id]);
        $this->post($lockedSubmitUrl, [
            'mahasiswa_nama' => 'Penyusup',
            'mahasiswa_nrp' => '123',
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ])->assertStatus(403);

        // Buku yang sudah memiliki pertemuan approved mengunci perubahan dosen_id & tidak boleh dihapus
        $this->actingAs($this->plp)->put(route('logbook.update', $book->id), [
            'lab_id' => $this->lab->id,
            'dosen_id' => $this->dosenKalab->id,
            'nama_mk' => $book->nama_mk,
            'kelas' => $book->kelas,
            'semester' => $book->semester,
            'tahun_akademik' => $book->tahun_akademik,
        ])->assertSessionHas('error');
        $book->refresh();
        $this->assertSame($this->dosenPengampu->id, $book->dosen_id);

        $this->actingAs($this->plp)->delete(route('logbook.destroy', $book->id))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('logbook_praktikums', ['id' => $book->id]);

        // --- B. MODE AUTO-APPROVE ATAS IZIN DOSEN ---
        // Harus gagal bila consent tidak dicentang
        $this->actingAs($this->dosenPengampu)->post(route('logbook.approval.auto-approve.save'), [
            'is_enabled' => '1',
        ])->assertRedirect();
        $this->assertFalse(DosenLogbookAutoApprove::isActiveFor($this->dosenPengampu->id, $this->plp->id));

        // Aktifkan dengan consent resmi
        $this->actingAs($this->dosenPengampu)->post(route('logbook.approval.auto-approve.save'), [
            'is_enabled' => '1',
            'consent' => '1',
        ])->assertRedirect();
        $this->assertTrue(DosenLogbookAutoApprove::isActiveFor($this->dosenPengampu->id, $this->plp->id));

        // Saat PLP submit p2 -> langsung approved otomatis
        $this->actingAs($this->plp)->post(route('logbook.pertemuan.submit', $p2->id))->assertRedirect();
        $p2->refresh();
        $this->assertSame(LogbookPertemuan::STATUS_APPROVED, $p2->status);
        $this->assertSame('auto', $p2->approval_mode);
        $this->assertNotNull($p2->approved_at);

        // Cetak PDF FM. LAB-02 (1 buku full & 1 pertemuan)
        $pdfResp = $this->actingAs($this->plp)->get(route('logbook.export-pdf', $book->id));
        $pdfResp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfResp->headers->get('content-type'));

        // Dosen membatalkan TTD (Revoke) pada p2
        $this->actingAs($this->dosenPengampu)->post(route('logbook.approval.revoke', $p2->id), [
            'revoked_reason' => 'Perlu perbaikan catatan masalah',
        ])->assertRedirect();
        $p2->refresh();
        $this->assertSame(LogbookPertemuan::STATUS_REJECTED, $p2->status);
        $this->assertNotNull($p2->revoked_at);
        $this->assertSame('Perlu perbaikan catatan masalah', $p2->revoked_reason);
    }
}

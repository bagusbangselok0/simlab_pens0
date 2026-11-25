<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class Phase3SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $dosen;
    private User $mhs;
    private Lab $lab;
    private LabManager $manager;
    private int $roleMhsId;
    private string $sigFile;
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $this->roleMhsId = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $jabPlp = DB::table('jabatans')->insertGetId(['id' => 4, 'nama_jabatan' => 'PLP', 'slug' => 'plp']);

        $this->sigFile = $this->createPrivateSig('phase3_sig_' . uniqid() . '.png');

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Phase3',
            'email' => 'admin-p3@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'jabatan_id' => $jabPlp,
            'nama_asli' => 'PLP Phase3',
            'email' => 'plp-p3@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $this->sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosen = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Dosen Pengampu Phase3',
            'email' => 'dosen-p3@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $this->sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mhs = User::create([
            'role_id' => $this->roleMhsId,
            'nama_asli' => 'Mahasiswa Phase3',
            'nrp' => '3125500301',
            'email' => 'mhs-p3@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $this->sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab Rekayasa Keamanan',
            'kode_lab' => 'L-P3-01',
            'lokasi' => 'Gedung D4',
        ]);

        $this->manager = LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
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

    private function assertSecurityHeadersPresent($response): void
    {
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $permissions = $response->headers->get('Permissions-Policy');
        $this->assertNotEmpty($permissions);
        $this->assertStringContainsString('camera=(), microphone=(), geolocation=()', $permissions);

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("img-src 'self' data: blob: https://api.qrserver.com", $csp);
        $this->assertStringContainsString("connect-src 'self' https://cdn.datatables.net", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_security_headers_are_applied_globally_including_binary_file_responses(): void
    {
        $guestLogin = $this->get(route('login'))->assertStatus(200);
        $this->assertSecurityHeadersPresent($guestLogin);

        $dashboard = $this->actingAs($this->mhs)->get(route('dashboard'))->assertStatus(200);
        $this->assertSecurityHeadersPresent($dashboard);

        $privateSig = $this->actingAs($this->admin)
            ->get(route('signatures.user.show', ['user' => $this->mhs->id]))
            ->assertStatus(200);
        $this->assertSecurityHeadersPresent($privateSig);
    }

    public function test_datatables_raw_columns_escape_xss_payloads(): void
    {
        $xssUser = User::create([
            'role_id' => $this->roleMhsId,
            'nama_asli' => '<script>alert("xss-user")</script>',
            'nrp' => '"><img src=x onerror=alert(1)>',
            'email' => 'xss-mhs@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $this->sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $labLoan = PeminjamanLab::create([
            'mahasiswa_id' => $xssUser->id,
            'lab_id' => $this->lab->id,
            'lab_manager_id' => $this->manager->id,
            'tujuan' => '<svg onload=alert("tujuan")>',
            'waktu_mulai' => Carbon::now()->addDay(),
            'waktu_selesai' => Carbon::now()->addDays(2),
            'ttd_mahasiswa_file' => $this->sigFile,
            'status' => 'ditolak',
        ]);
        $labLoan->catatan_tolak = '<script>alert("tolak-lab")</script>';
        $labLoan->save();

        $loanAlat = PeminjamanAlat::create([
            'kode_peminjaman' => 'PA-XSS-001',
            'mahasiswa_id' => $xssUser->id,
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'keperluan' => '<script>alert("keperluan")</script>',
            'waktu_pinjam' => Carbon::now()->addHour(),
            'rencana_kembali' => Carbon::now()->addDay(),
            'status' => 'ditolak',
            'catatan_penolakan' => '<svg onload=alert("tolak-alat")>',
            'ttd_mahasiswa_file' => $this->sigFile,
        ]);

        PeminjamanAlatDetail::create([
            'peminjaman_alat_id' => $loanAlat->id,
            'nama_alat' => '<img src=x onerror=alert("alat-manual")>',
            'jumlah' => 1,
            'satuan' => '<b>Unit</b>',
            'is_manual' => true,
        ]);

        // 1. Admin DataTables Peminjaman Lab
        $adminDt = $this->actingAs($this->admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('peminjaman.admin'))
            ->assertStatus(200);

        $adminMahasiswaHtml = (string) ($adminDt->json('data.0.mahasiswa') ?? '');
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss-user&quot;)&lt;/script&gt;', $adminMahasiswaHtml);
        $this->assertStringNotContainsString('<script>', $adminMahasiswaHtml);
        $this->assertStringNotContainsString('<img src=x', $adminMahasiswaHtml);

        // 2. PLP DataTables Approval Peminjaman Alat
        $plpDt = $this->actingAs($this->plp)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('peminjaman-alat.approval'))
            ->assertStatus(200);

        $plpMahasiswaHtml = (string) ($plpDt->json('data.0.mahasiswa') ?? '');
        $plpDaftarAlatHtml = (string) ($plpDt->json('data.0.daftar_alat') ?? '');
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss-user&quot;)&lt;/script&gt;', $plpMahasiswaHtml);
        $this->assertStringNotContainsString('<script>', $plpMahasiswaHtml);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(&quot;alat-manual&quot;)&gt;', $plpDaftarAlatHtml);
        $this->assertStringContainsString('&lt;b&gt;Unit&lt;/b&gt;', $plpDaftarAlatHtml);
        $this->assertStringNotContainsString('<img', $plpDaftarAlatHtml);
        $this->assertStringNotContainsString('<b>Unit</b>', $plpDaftarAlatHtml);

        // 3. Mahasiswa DataTables Peminjaman Alat
        $mhsDt = $this->actingAs($xssUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('peminjaman-alat.index'))
            ->assertStatus(200);

        $mhsDaftarAlatHtml = (string) ($mhsDt->json('data.0.daftar_alat') ?? '');
        $mhsKeperluan = (string) ($mhsDt->json('data.0.keperluan') ?? '');
        $this->assertStringContainsString('&lt;img src=x onerror=alert(&quot;alat-manual&quot;)&gt;', $mhsDaftarAlatHtml);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;keperluan&quot;)&lt;/script&gt;', $mhsKeperluan);
        $this->assertStringNotContainsString('<img', $mhsDaftarAlatHtml);
        $this->assertStringNotContainsString('<script>', $mhsKeperluan);
    }

    public function test_blade_views_escape_user_inputs_in_logbook_and_dashboard(): void
    {
        $book = LogbookPraktikum::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'dosen_id' => $this->dosen->id,
            'nama_mk' => 'Keamanan Aplikasi Web',
            'kelas' => '3 D4 IT',
            'semester' => 'Ganjil',
            'tahun_akademik' => '2026/2027',
        ]);

        LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Audit XSS',
            'tanggal_pelaksanaan' => '2026-09-10',
            'jam_pelaksanaan' => '08:00 WIB',
            'peralatan' => [
                [
                    'nama_alat' => '<img src=x onerror=alert("logbook-alat")>',
                    'kategori' => 'Alat 1',
                    'jumlah' => '1',
                    'kondisi' => 'Baik',
                ],
            ],
            'aktivitas' => ['<script>alert("aktivitas")</script>'],
            'permasalahan' => [
                [
                    'masalah' => '<svg onload=alert("masalah")>',
                    'solusi' => '<iframe src="javascript:alert(1)"></iframe>',
                ],
            ],
            'status' => LogbookPertemuan::STATUS_PENDING_DOSEN,
            'submitted_at' => now(),
        ]);

        $approvalPage = $this->actingAs($this->dosen)->get(route('logbook.approval.index'));
        $approvalPage->assertStatus(200)
            ->assertDontSee('<script>alert("aktivitas")</script>', false)
            ->assertDontSee('<svg onload=alert("masalah")>', false)
            ->assertDontSee('<iframe src="javascript:alert(1)"></iframe>', false)
            ->assertDontSee('<img src=x onerror=alert("logbook-alat")>', false)
            ->assertSee('&lt;script&gt;alert(&quot;aktivitas&quot;)&lt;/script&gt;', false);

        $loanAlat = PeminjamanAlat::create([
            'kode_peminjaman' => 'PA-DASH-XSS',
            'mahasiswa_id' => $this->mhs->id,
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'keperluan' => '<script>alert("dash-keperluan")</script>',
            'waktu_pinjam' => Carbon::now()->addHour(),
            'rencana_kembali' => Carbon::now()->addDay(),
            'status' => 'ditolak',
            'catatan_penolakan' => '<svg onload=alert("tolak-alat")>',
            'ttd_mahasiswa_file' => $this->sigFile,
        ]);

        PeminjamanAlatDetail::create([
            'peminjaman_alat_id' => $loanAlat->id,
            'nama_alat' => '<img src=x onerror=alert("alat-manual")>',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'is_manual' => true,
        ]);

        $dashboard = $this->actingAs($this->mhs)->get(route('dashboard'));
        $dashboard->assertStatus(200)
            ->assertDontSee('<img src=x onerror=alert("alat-manual")>', false)
            ->assertDontSee('<svg onload=alert("tolak-alat")>', false)
            ->assertDontSee('<script>alert("dash-keperluan")</script>', false);
    }

    public function test_session_hardening_and_login_session_regeneration(): void
    {
        $this->assertTrue((bool) config('session.encrypt'));
        $this->assertTrue((bool) config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));

        $this->get(route('login'))->assertStatus(200);
        $beforeSessionId = session()->getId();
        $this->assertNotEmpty($beforeSessionId);

        $loginResponse = $this->post(route('login.post'), [
            'email' => $this->mhs->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->mhs);
        $this->assertNotSame($beforeSessionId, session()->getId());
    }
}

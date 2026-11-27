<?php

namespace Tests\Feature;

use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\PeminjamanLab;
use App\Models\PeriodeJadwal;
use App\Models\User;
use App\Support\SecurityAudit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSX;
use Tests\TestCase;

class Phase4SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $dosen;
    private User $mhs;
    private Lab $lab;
    private LabManager $manager;
    private int $prodiId;
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $roleSatpam = DB::table('roles')->insertGetId(['nama_role' => 'Satpam', 'slug' => 'satpam']);
        $roleMhs = DB::table('roles')->insertGetId(['id' => 5, 'nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $jurusanId = DB::table('jurusan')->insertGetId([
            'nama_jurusan' => 'Teknik Informatika',
            'kode_jurusan' => 'TI',
        ]);
        $this->prodiId = DB::table('prodi')->insertGetId([
            'nama_prodi' => 'D3 Teknik Informatika',
            'kode_prodi' => 'D3TI',
            'jurusan_id' => $jurusanId,
        ]);

        $sigFile = $this->createPrivateSig('phase4_sig_' . uniqid() . '.png');

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Phase4',
            'email' => 'admin-p4@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Phase4',
            'email' => 'plp-p4@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosen = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Dosen Phase4',
            'email' => 'dosen-p4@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mhs = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => '+SUM(1,2)',
            'nrp' => '@NRP123',
            'prodi_id' => $this->prodiId,
            'email' => 'mhs-p4@test.com',
            'password' => bcrypt('password'),
            'signature_path' => $sigFile,
            'signature_status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab Jaringan Phase4',
            'kode_lab' => 'L-P4-01',
            'lokasi' => 'Gedung B',
        ]);

        $this->manager = LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
        ]);
    }

    protected function tearDown(): void
    {
        $freshSig = $this->mhs->fresh()?->signature_path;
        if ($freshSig) {
            $freshPath = Storage::disk('local')->path('signatures/' . $freshSig);
            if (is_file($freshPath)) {
                $this->cleanupPaths[] = $freshPath;
            }
        }

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

    public function test_excel_exports_neutralize_formula_injection_payloads(): void
    {
        $sig = $this->mhs->signature_path;
        PeminjamanLab::create([
            'mahasiswa_id' => $this->mhs->id,
            'lab_id' => $this->lab->id,
            'lab_manager_id' => $this->manager->id,
            'tujuan' => "=CMD|' /C calc'!A0\n<f>SUM(1,2)</f>",
            'waktu_mulai' => Carbon::now()->addDay(),
            'waktu_selesai' => Carbon::now()->addDays(2),
            'ttd_mahasiswa_file' => $sig,
            'status' => 'disetujui',
        ]);

        $adminExport = $this->actingAs($this->admin)->get(route('peminjaman.admin.export-excel'));
        $adminExport->assertStatus(200);

        $xlsx = SimpleXLSX::parseData($adminExport->getContent());
        $this->assertNotFalse($xlsx);
        $rows = $xlsx->rows();

        // Baris index 10 adalah baris data pertama setelah KOP (0..8), baris kosong (9), header tabel (10 -> wait, data di 11)
        $dataRow = collect($rows)->first(fn ($r) => isset($r[1]) && str_contains((string) $r[1], 'SUM(1,2)'));
        $this->assertNotNull($dataRow);
        $this->assertSame("'+SUM(1,2)", $dataRow[1]);
        $this->assertSame("'@NRP123", $dataRow[2]);
        $this->assertSame("'=CMD|' /C calc'!A0 <f>SUM(1,2)</f>", $dataRow[4]);

        if (class_exists(\ZipArchive::class)) {
            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'p4_admin_' . uniqid() . '.xlsx';
            file_put_contents($tmp, $adminExport->getContent());
            $this->cleanupPaths[] = $tmp;
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tmp) === true);
            $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();
            $this->assertStringNotContainsString('<f>', $sheetXml);
        }

        // Export Excel Jadwal Lab
        $periode = PeriodeJadwal::create([
            'nama_periode' => 'Semester Ganjil 2026/2027',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'is_active' => true,
            'uploaded_by' => $this->admin->id,
        ]);

        JadwalLab::create([
            'periode_jadwal_id' => $periode->id,
            'lab_id' => $this->lab->id,
            'nama_ruangan' => 'Lab L-P4',
            'hari' => 'Senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 2,
            'waktu_mulai' => '08.00',
            'waktu_berakhir' => '09.40',
            'mata_kuliah' => '=HYPERLINK("http://evil.com","Click")',
            'jenis' => 'Praktek',
            'kelas' => '2 D3 IT',
            'semester' => '3',
            'program_studi' => 'TI',
            'nama_dosen' => '-2+3',
            'catatan' => '@IMPORTXML(A1,B1)',
        ]);

        $jadwalExport = $this->actingAs($this->mhs)->get(route('jadwal-lab.export-excel', ['periode_id' => $periode->id]));
        $jadwalExport->assertStatus(200);

        $jadwalXlsx = SimpleXLSX::parseData($jadwalExport->getContent());
        $this->assertNotFalse($jadwalXlsx);
        $jadwalRows = $jadwalXlsx->rows();

        $seninRow = $jadwalRows[4] ?? [];
        $this->assertSame('\'=HYPERLINK("http://evil.com","Click")', $seninRow[1] ?? null);
        $this->assertSame("'-2+3", $seninRow[4] ?? null);
        $this->assertSame("'@IMPORTXML(A1,B1)", $seninRow[7] ?? null);

        // Baris Selasa (hari kosong) tetap menampilkan placeholder '-' tanpa kutip tambahan
        $selasaRow = $jadwalRows[5] ?? [];
        $this->assertSame('-', $selasaRow[1] ?? null);
    }

    public function test_excel_imports_reject_oversized_and_spoofed_mime_files(): void
    {
        // 1. File melebihi batas 5 MB (6 MB)
        $oversized = UploadedFile::fake()->create(
            'huge.xlsx',
            6000,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->actingAs($this->plp)
            ->post(route('inventaris.import'), ['file' => $oversized])
            ->assertSessionHasErrors('file');

        $this->actingAs($this->plp)
            ->post(route('jadwal-lab.preview-import'), ['file' => $oversized])
            ->assertSessionHasErrors('file');

        // 2. Payload skrip PHP disamarkan menjadi ekstensi .xlsx
        $spoofedPhp = UploadedFile::fake()->createWithContent('shell.xlsx', '<?php system($_GET["c"]); ?>');

        $this->actingAs($this->plp)
            ->post(route('inventaris.import'), ['file' => $spoofedPhp])
            ->assertSessionHasErrors('file');

        $this->actingAs($this->plp)
            ->post(route('jadwal-lab.preview-import'), ['file' => $spoofedPhp])
            ->assertSessionHasErrors('file');

        // 3. Arsip ZIP biasa (tanpa xl/workbook.xml) yang diubah ekstensinya menjadi .xlsx
        if (class_exists(\ZipArchive::class)) {
            $tmpZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'not_excel_' . uniqid() . '.zip';
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true);
            $zip->addFromString('notes.txt', 'bukan file workbook excel');
            $zip->close();
            $this->cleanupPaths[] = $tmpZip;

            $fakeZipAsXlsx = new UploadedFile($tmpZip, 'not_excel.xlsx', 'application/zip', null, true);

            $this->actingAs($this->plp)
                ->post(route('inventaris.import'), ['file' => $fakeZipAsXlsx])
                ->assertSessionHasErrors('file');

            $this->actingAs($this->plp)
                ->post(route('jadwal-lab.preview-import'), ['file' => $fakeZipAsXlsx])
                ->assertSessionHasErrors('file');
        }
    }

    public function test_sensitive_routes_enforce_strict_rate_limiting(): void
    {
        // 1. Login: maks 5 per menit per email + IP -> percobaan ke-6 kena 429
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.1'])
                ->post(route('login.post'), [
                    'email' => $this->mhs->email,
                    'password' => 'wrong-pass',
                ])
                ->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.1'])
            ->post(route('login.post'), [
                'email' => $this->mhs->email,
                'password' => 'wrong-pass',
            ])
            ->assertStatus(429);

        // 2. Register: maks 5 per menit per IP -> percobaan ke-6 kena 429
        for ($i = 1; $i <= 5; $i++) {
            $resp = $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.2'])
                ->post(route('register.post'), [
                    'nama_asli' => 'Calon Mahasiswa ' . $i,
                    'nrp' => '312599000' . $i,
                    'email' => "reg-p4-{$i}@test.com",
                    'prodi_id' => $this->prodiId,
                    'password' => 'PasswordKu123',
                    'password_confirmation' => 'PasswordKu123',
                ]);
            $this->assertTrue(in_array($resp->getStatusCode(), [302, 422], true));
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.2'])
            ->post(route('register.post'), [
                'nama_asli' => 'Calon Mahasiswa 6',
                'nrp' => '3125990006',
                'email' => 'reg-p4-6@test.com',
                'prodi_id' => $this->prodiId,
                'password' => 'PasswordKu123',
                'password_confirmation' => 'PasswordKu123',
            ])
            ->assertStatus(429);

        // 3. Upload TTD: maks 6 per menit per user -> percobaan ke-7 kena 429
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        for ($i = 1; $i <= 6; $i++) {
            $sigImg = UploadedFile::fake()->createWithContent("ttd_{$i}.png", $pngBinary);
            $this->actingAs($this->mhs)
                ->withHeaders(['Accept' => 'application/json'])
                ->post(route('profile.upload_ttd', $this->mhs->id), [
                    'signature_path' => $sigImg,
                ])
                ->assertStatus(200);
        }

        $this->actingAs($this->mhs)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('profile.upload_ttd', $this->mhs->id), [
                'signature_path' => UploadedFile::fake()->createWithContent('ttd_7.png', $pngBinary),
            ])
            ->assertStatus(429);

        // 4. Reset password: maks 5 per menit per email + IP -> percobaan ke-6 kena 429
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.4'])
                ->post(route('password.update'), [
                    'token' => 'invalid-reset-token',
                    'email' => $this->mhs->email,
                    'password' => 'NewSecret123!',
                    'password_confirmation' => 'NewSecret123!',
                ])
                ->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.4'])
            ->post(route('password.update'), [
                'token' => 'invalid-reset-token',
                'email' => $this->mhs->email,
                'password' => 'NewSecret123!',
                'password_confirmation' => 'NewSecret123!',
            ])
            ->assertStatus(429);
    }

    public function test_security_audit_trail_logs_events_and_redacts_secrets(): void
    {
        Log::spy();

        $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.99'])
            ->post(route('login.post'), [
                'email' => $this->admin->email,
                'password' => 'MySuperSecretWrongPass!',
            ])
            ->assertStatus(302);

        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context = []) {
                return str_contains((string) $message, '[SECURITY_AUDIT] login_failed')
                    && ($context['attempted_email'] ?? null) === $this->admin->email
                    && !str_contains((string) json_encode($context), 'MySuperSecretWrongPass!');
            })
            ->atLeast()
            ->once();

        $this->withServerVariables(['REMOTE_ADDR' => '10.88.0.99'])
            ->post(route('login.post'), [
                'email' => $this->admin->email,
                'password' => 'password',
            ])
            ->assertRedirect('/dashboard');

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context = []) {
                return str_contains((string) $message, '[SECURITY_AUDIT] login_success')
                    && ($context['target_user_id'] ?? null) === $this->admin->id;
            })
            ->atLeast()
            ->once();

        SecurityAudit::log('secret_redaction_probe', [
            'password' => 'SecretPassword123',
            'token' => 'SecretToken456',
            'safe_field' => 'ok',
        ]);

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context = []) {
                return str_contains((string) $message, '[SECURITY_AUDIT] secret_redaction_probe')
                    && ($context['password'] ?? null) === '[REDACTED]'
                    && ($context['token'] ?? null) === '[REDACTED]'
                    && ($context['safe_field'] ?? null) === 'ok';
            })
            ->atLeast()
            ->once();
    }
}

<?php

namespace Tests\Feature;

use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\PeminjamanAlat;
use App\Models\PeminjamanAlatDetail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase1SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $dosen;
    private User $mahasiswa;
    private User $otherMahasiswa;
    private Lab $lab;
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $roleMhs = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Keamanan',
            'email' => 'admin-sec@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Keamanan',
            'nip' => '199001012020121001',
            'email' => 'plp-sec@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosen = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Dosen Pengampu Sec',
            'nip' => '198801012015121001',
            'email' => 'dosen-sec@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => 'Mahasiswa Rahasia Satu',
            'nrp' => '3125500001',
            'email' => 'mhs1-sec@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->otherMahasiswa = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => 'Mahasiswa Dua',
            'nrp' => '3125500002',
            'email' => 'mhs2-sec@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab Jaringan',
            'kode_lab' => 'L-SEC-01',
            'lokasi' => 'Gedung A',
        ]);

        LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'kalab_id' => $this->dosen->id,
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

    public function test_user_signature_is_stored_in_private_storage_with_strict_authorization(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $file = UploadedFile::fake()->createWithContent('ttd_saya.png', $pngBinary);

        $uploadResp = $this->actingAs($this->mahasiswa)->post(route('profile.upload_ttd', $this->mahasiswa->id), [
            'signature_path' => $file,
        ]);

        $uploadResp->assertStatus(200)->assertJson(['success' => true]);

        $this->mahasiswa->refresh();
        $filename = $this->mahasiswa->signature_path;
        $this->assertNotNull($filename);
        $this->assertStringNotContainsString('Rahasia', $filename);

        $privateFullPath = Storage::disk('local')->path('signatures/' . $filename);
        $this->cleanupPaths[] = $privateFullPath;

        $this->assertFileExists($privateFullPath);
        $this->assertFileDoesNotExist(public_path('storage/signatures/' . $filename));
        $this->assertFileDoesNotExist(storage_path('app/public/signatures/' . $filename));

        $previewUrl = route('signatures.user.show', ['user' => $this->mahasiswa->id]);

        // Guest ditolak (redirect ke login)
        Auth::logout();
        $this->get($previewUrl)->assertRedirect(route('login'));

        // User lain (mahasiswa lain & dosen) ditolak 403
        $this->actingAs($this->otherMahasiswa)->get($previewUrl)->assertStatus(403);
        $this->actingAs($this->dosen)->get($previewUrl)->assertStatus(403);

        // Pemilik akun & Admin diizinkan dengan header no-store
        $ownerResp = $this->actingAs($this->mahasiswa)->get($previewUrl);
        $ownerResp->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $ownerResp->headers->get('cache-control'));

        $this->actingAs($this->admin)->get($previewUrl)->assertStatus(200);
    }

    public function test_logbook_student_signature_and_pdf_generation_work_from_private_storage(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        $plpSigName = 'sig_plp_pdf_' . uniqid() . '.png';
        $mhsSigName = 'sig_mhs_pdf_' . uniqid() . '.png';
        Storage::disk('local')->put('signatures/' . $plpSigName, $pngBinary);
        Storage::disk('local')->put('signatures/' . $mhsSigName, $pngBinary);
        $this->cleanupPaths[] = Storage::disk('local')->path('signatures/' . $plpSigName);
        $this->cleanupPaths[] = Storage::disk('local')->path('signatures/' . $mhsSigName);

        $this->plp->update(['signature_path' => $plpSigName, 'signature_status' => 'approved']);
        $this->mahasiswa->update(['signature_path' => $mhsSigName, 'signature_status' => 'approved']);

        // 1. Test Cetak PDF Peminjaman Alat membaca TTD dari private storage
        $inv = InventarisRuangan::create([
            'lab_id' => $this->lab->id,
            'kode_barang' => 'K-01',
            'nup' => '1',
            'nama_barang' => 'Oscilloscope',
            'spesifikasi_merk_tipe' => 'Rigol',
            'tahun_perolehan' => 2025,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
            'is_bisa_dipinjam' => true,
        ]);

        $loan = PeminjamanAlat::create([
            'kode_peminjaman' => 'PA-SEC-001',
            'mahasiswa_id' => $this->mahasiswa->id,
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'keperluan' => 'Uji Keamanan PDF',
            'waktu_pinjam' => now(),
            'rencana_kembali' => now()->addDay(),
            'status' => 'disetujui',
            'ttd_mahasiswa_file' => $mhsSigName,
            'ttd_plp_file' => $plpSigName,
        ]);

        PeminjamanAlatDetail::create([
            'peminjaman_alat_id' => $loan->id,
            'inventaris_ruangan_id' => $inv->id,
            'is_manual' => false,
            'kode_barang' => 'K-01',
            'nup' => '1',
            'nama_alat' => 'Oscilloscope',
            'spesifikasi' => 'Rigol',
            'jumlah' => 1,
            'satuan' => 'Unit',
        ]);

        $pdfAlatResp = $this->actingAs($this->mahasiswa)->get(route('peminjaman-alat.cetak', $loan->id));
        $pdfAlatResp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfAlatResp->headers->get('content-type'));

        // 2. Test TTD Mahasiswa di Logbook disimpan di private storage & route preview terproteksi
        $book = LogbookPraktikum::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'dosen_id' => $this->dosen->id,
            'nama_mk' => 'Keamanan Sistem',
            'kelas' => '3 D3 IT',
            'semester' => 'Genap',
            'tahun_akademik' => '2026/2027',
        ]);

        $this->actingAs($this->plp)->post(route('logbook.mahasiswa-signature'), [
            'target_type' => 'logbook',
            'target_id' => $book->id,
            'mahasiswa_nama' => 'Perwakilan Kelas',
            'mahasiswa_nrp' => '3125500001',
            'signature_data' => 'data:image/png;base64,' . base64_encode($pngBinary),
        ])->assertRedirect(route('logbook.show', $book->id));

        $book->refresh();
        $this->assertNotNull($book->default_mahasiswa_ttd_path);
        $privateLogbookPath = Storage::disk('local')->path('logbook_signatures/' . $book->default_mahasiswa_ttd_path);
        $this->cleanupPaths[] = $privateLogbookPath;
        $this->assertFileExists($privateLogbookPath);

        $logbookPreviewUrl = route('signatures.logbook.show', ['type' => 'logbook', 'id' => $book->id]);
        $this->actingAs($this->mahasiswa)->get($logbookPreviewUrl)->assertStatus(403);
        $this->actingAs($this->plp)->get($logbookPreviewUrl)->assertStatus(200);
        $this->actingAs($this->dosen)->get($logbookPreviewUrl)->assertStatus(200);
        $this->actingAs($this->admin)->get($logbookPreviewUrl)->assertStatus(200);

        LogbookPertemuan::create([
            'logbook_praktikum_id' => $book->id,
            'minggu_ke' => 1,
            'judul_praktikum' => 'Audit Storage',
            'tanggal_pelaksanaan' => '2026-09-01',
            'jam_pelaksanaan' => '08:00 WIB',
            'peralatan' => [['nama_alat' => 'Switch', 'kategori' => 'Alat 1', 'jumlah' => '1', 'kondisi' => 'Baik']],
            'status' => LogbookPertemuan::STATUS_APPROVED,
        ]);

        $pdfLogbookResp = $this->actingAs($this->plp)->get(route('logbook.export-pdf', $book->id));
        $pdfLogbookResp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfLogbookResp->headers->get('content-type'));
    }

    public function test_settings_secrets_are_encrypted_in_database_and_masked_in_ui(): void
    {
        $secretToken = 'fonnte-super-secret-token-9988';
        $secretVapid = 'vapid-super-secret-private-key-7766';

        $this->actingAs($this->admin)
            ->post(route('settings.notification-gateway'), [
                'wa_enabled' => '1',
                'wa_token' => $secretToken,
                'wa_api_url' => 'https://api.fonnte.com/send',
                'push_enabled' => '1',
                'vapid_subject' => 'mailto:admin@simlab.com',
                'vapid_public_key' => 'BPublicTestKeyExample123456',
                'vapid_private_key' => $secretVapid,
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'notif']));

        $waRow = Setting::where('setting_key', Setting::WA_TOKEN)->firstOrFail();
        $vapidRow = Setting::where('setting_key', Setting::VAPID_PRIVATE_KEY)->firstOrFail();

        // Nilai di kolom DB wajib terenkripsi (bukan plaintext)
        $this->assertSame('encrypted', $waRow->value_type);
        $this->assertSame('encrypted', $vapidRow->value_type);
        $this->assertNotSame($secretToken, $waRow->value);
        $this->assertNotSame($secretVapid, $vapidRow->value);
        $this->assertSame($secretToken, Crypt::decryptString($waRow->value));
        $this->assertSame($secretVapid, Crypt::decryptString($vapidRow->value));

        // Setting::get() mendekripsi secara transparan saat digunakan service
        $this->assertSame($secretToken, Setting::get(Setting::WA_TOKEN));
        $this->assertSame($secretVapid, Setting::get(Setting::VAPID_PRIVATE_KEY));

        // UI /settings tidak boleh memuat plaintext token/private key
        $settingsPage = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'notif']));
        $settingsPage->assertStatus(200);
        $settingsPage->assertDontSee($secretToken);
        $settingsPage->assertDontSee($secretVapid);
        $settingsPage->assertSee('Tersimpan (Terenkripsi)');

        // Submit ulang tanpa mengisi ulang secret tidak menimpa secret terenkripsi yang ada
        $this->actingAs($this->admin)
            ->post(route('settings.notification-gateway'), [
                'wa_enabled' => '1',
                'wa_token' => '',
                'wa_api_url' => 'https://api.fonnte.com/send',
                'push_enabled' => '1',
                'vapid_subject' => 'mailto:admin@simlab.com',
                'vapid_public_key' => 'BPublicTestKeyExample123456',
                'vapid_private_key' => '',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'notif']));

        $this->assertSame($secretToken, Setting::get(Setting::WA_TOKEN));
        $this->assertSame($secretVapid, Setting::get(Setting::VAPID_PRIVATE_KEY));
    }
}

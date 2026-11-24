<?php

namespace Tests\Feature;

use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\PeriodeJadwal;
use App\Models\User;
use App\Services\JadwalPerkuliahanParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Tests\TestCase;

class JadwalLabTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $dosenLusiana;
    private User $mahasiswa;
    private Lab $lab201;
    private Lab $lab203;
    private Lab $studio;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $roleDosen = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleMahasiswa = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Test',
            'email' => 'admin-jadwal@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Test',
            'nip' => '198501012010121001',
            'email' => 'plp-jadwal@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosenLusiana = User::create([
            'role_id' => $roleDosen,
            'nama_asli' => 'Lusiana Agustien',
            'gelar_belakang' => 'S.Kom., M.Kom.',
            'nip' => '198001012005122001',
            'email' => 'lusiana@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMahasiswa,
            'nama_asli' => 'Mahasiswa Test',
            'nrp' => '3125500001',
            'email' => 'mhs-jadwal@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab201 = Lab::create([
            'nama_lab' => 'Lab Editing Video',
            'kode_lab' => 'L-201',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);

        $this->lab203 = Lab::create([
            'nama_lab' => 'Lab Database',
            'kode_lab' => 'L-203',
            'lokasi' => 'PSDKU Sumenep, Lantai 2',
        ]);

        $this->studio = Lab::create([
            'nama_lab' => 'Studio Multimedia',
            'kode_lab' => 'K-101',
            'lokasi' => 'PSDKU Sumenep, Lantai 1',
        ]);
    }

    /**
     * Buat file Excel uji dengan struktur persis format Jadwal Perkuliahan.
     */
    private function createSamplePerkuliahanXlsx(bool $withConflict = false): string
    {
        // Sheet 1: D3MMB (memiliki typo 1D3MMN, PSDKU-SM Studio, serta Idem melewati istirahat Jam 5 -> 7)
        $mmbRows = [
            ['', '', '', '', ''],
            ['JADWAL KULIAH SEMESTER GASAL TAHUN AKADEMIK 2026/2027', '', '', '', ''],
            ['PROGRAM STUDI DIPLOMA 3 MULTIMEDIA BROADCASTING - KAMPUS SUMENEP', '', '', '', ''],
            ['', '', '', '', ''],
            ['H A R I', 'JAM', 'S E M E S T E R    1', 'S E M E S T E R    3', ''],
            ['', 'KE', '1D3MMN - PSDKU SM', '3D3MMB- PSDKU SM', ''],
            ['', '', '', '', ''],                                         // r=6
            ['', '1.0', '', '', ''],                                        // r=7 (Jam 1)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '2.0', '', '', ''],                                        // r=10 (Jam 2)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '3.0', '', '', ''],                                        // r=13 (Jam 3)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '4.0', '', '', ''],                                        // r=16 (Jam 4)
            ['', '', '', '', ''],
            ['', '', 'Teori & Praktek', '', ''],                            // r=18 (jenis sebelum Jam 5)
            ['', '5.0', 'Workshop Literasi Multimedia', '', ''],            // r=19 (Jam 5)
            ['SENIN', '', 'Deny Fardiansyah Putra', '', ''],
            ['', '', 'PSDKU-SM L-201', '', ''],
            ['', '6.0', 'Idem', '', ''],                                    // r=22 (Jam 6)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '7.0', 'Idem', '', ''],                                    // r=25 (Jam 7)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '8.0', '', '', ''],                                        // r=28 (Jam 8)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            // SELASA
            ['', '', 'Teori & Praktek', '', ''],                            // r=31 (jenis sebelum Jam 1 Selasa)
            ['', '1.0', 'Workshop Fotografi', '', ''],                      // r=32 (Jam 1 Selasa)
            ['SELASA', '', 'Ahmad Walid Hujairi', '', ''],
            ['', '', 'PSDKU-SM Studio', '', ''],
            ['', '2.0', 'Idem', '', ''],                                    // r=35 (Jam 2 Selasa)
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '3.0', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', 'Hari Senin s/d Kamis', "Hari Jum'at", ''],
        ];

        // Sheet 2: D3TI (memiliki auto-match dosen Lusiana Agustien, PSDKU-L 203, PSDKU-SM K.102, dan opsional bentrok)
        $tiRows = [
            ['', '', '', '', ''],
            ['JADWAL KULIAH SEMESTER GASAL TAHUN AKADEMIK 2026/2027', '', '', '', ''],
            ['PROGRAM STUDI DIPLOMA 3 TEKNIK INFORMATIKA - KAMPUS SUMENEP', '', '', '', ''],
            ['', '', '', '', ''],
            ['H A R I', 'JAM', 'S E M E S T E R    1', 'S E M E S T E R    3', ''],
            ['', 'KE', '1D3TI - PSDKU SM', '3D3TI- PSDKU SM', ''],
            ['', '', '', 'Teori', ''],                                      // r=6
            ['', '1.0', '', 'Basis Data Lanjut', ''],                       // r=7 (Jam 1 Senin)
            ['', '', '', 'Lusiana Agustien', ''],
            ['', '', '', 'PSDKU-SM L-201', ''],
            ['', '2.0', '', 'Idem', ''],                                    // r=10 (Jam 2 Senin)
            ['', '', '', '', ''],
            ['', '', 'Teori', '', ''],                                      // r=12
            ['', '3.0', 'Konsep Pemrograman', '', ''],                      // r=13 (Jam 3 Senin)
            ['', '', 'Lusiana Agustien', '', ''],
            ['', '', 'PSDKU-L 203', '', ''],
            ['', '4.0', 'Idem', '', ''],                                    // r=16 (Jam 4 Senin)
            ['', '', '', '', ''],
            ['', '', '', 'Praktek', ''],                                    // r=18
            ['', '5.0', '', $withConflict ? 'Bentrok L-201' : 'Praktikum Basis Data Lanjut', ''],
            ['SENIN', '', '', 'Lusiana Agustien', ''],
            ['', '', 'Teori', $withConflict ? 'PSDKU-SM L-201' : 'PSDKU-SM K.102', ''],
            ['', '6.0', 'Dasar Sistem Informasi', 'Idem', ''],
            ['', '', 'Joko Prasetyo', '', ''],
            ['', '', 'PSDKU-SM K-102', '', ''],
            ['', '7.0', 'Idem', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', 'Hari Senin s/d Kamis', "Hari Jum'at", ''],
        ];

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_jadwal_' . uniqid() . '.xlsx';
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($mmbRows, 'D3MMB PSDKU-SM');
        $xlsx->addSheet($tiRows, 'D3TI PSDKU-SM');
        $xlsx->saveAs($path);

        return $path;
    }

    public function test_parser_converts_multi_sheet_schedule_accurately_and_detects_conflicts(): void
    {
        $filePath = $this->createSamplePerkuliahanXlsx(true);

        try {
            $parser = app(JadwalPerkuliahanParser::class);
            $result = $parser->parse($filePath);

            $this->assertSame('Ganjil', $result['meta']['semester']);
            $this->assertSame('2026/2027', $result['meta']['tahun_ajaran']);
            $this->assertSame('Semester Ganjil 2026/2027', $result['meta']['nama_periode']);

            $entries = collect($result['entries']);

            // Cek penggabungan Idem melewati istirahat 12.10-13.00 (Jam 5 s.d. Jam 7 => 11.20 - 14.40) + kebal typo 1D3MMN
            $literasi = $entries->firstWhere('mata_kuliah', 'Workshop Literasi Multimedia');
            $this->assertNotNull($literasi);
            $this->assertSame('Lab L-201', $literasi['nama_ruangan']);
            $this->assertSame($this->lab201->id, $literasi['lab_id']);
            $this->assertSame('Senin', $literasi['hari']);
            $this->assertSame('11.20', $literasi['waktu_mulai']);
            $this->assertSame('14.40', $literasi['waktu_berakhir']);
            $this->assertSame('MMB', $literasi['program_studi']);
            $this->assertSame('1', $literasi['semester']);
            $this->assertSame('Teori & Praktek', $literasi['jenis']);

            // Cek auto-match Studio & hari Selasa
            $fotografi = $entries->firstWhere('mata_kuliah', 'Workshop Fotografi');
            $this->assertNotNull($fotografi);
            $this->assertSame('Studio', $fotografi['nama_ruangan']);
            $this->assertSame($this->studio->id, $fotografi['lab_id']);
            $this->assertSame('Selasa', $fotografi['hari']);
            $this->assertSame('08.00', $fotografi['waktu_mulai']);
            $this->assertSame('09.40', $fotografi['waktu_berakhir']);

            // Cek normalisasi PSDKU-L 203 & auto-match dosen Lusiana Agustien
            $konsepProg = $entries->firstWhere('mata_kuliah', 'Konsep Pemrograman');
            $this->assertNotNull($konsepProg);
            $this->assertSame('Lab L-203', $konsepProg['nama_ruangan']);
            $this->assertSame($this->lab203->id, $konsepProg['lab_id']);
            $this->assertSame($this->dosenLusiana->id, $konsepProg['dosen_id']);

            // Cek normalisasi PSDKU-SM K-102 sebagai non-lab
            $dsi = $entries->firstWhere('mata_kuliah', 'Dasar Sistem Informasi');
            $this->assertNotNull($dsi);
            $this->assertSame('Kelas K-102', $dsi['nama_ruangan']);
            $this->assertNull($dsi['lab_id']);

            // Cek deteksi bentrok (Workshop Literasi Multimedia 11.20-14.40 vs Bentrok L-201 11.20-13.50)
            $this->assertGreaterThanOrEqual(1, $result['stats']['total_conflicts']);
            $this->assertTrue($literasi['is_conflict']);
        } finally {
            @unlink($filePath);
        }
    }

    public function test_admin_and_plp_can_preview_commit_and_crud_jadwal_lab(): void
    {
        $filePath = $this->createSamplePerkuliahanXlsx(false);

        try {
            $uploaded = new UploadedFile(
                $filePath,
                'Jadwal Perkuliahan Ganjil 2026_2027.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $previewResp = $this->actingAs($this->plp)->post(route('jadwal-lab.preview-import'), [
                'file' => $uploaded,
            ]);
            $previewResp->assertRedirect(route('jadwal-lab.preview-show'));

            $showPreviewResp = $this->actingAs($this->plp)->get(route('jadwal-lab.preview-show'));
            $showPreviewResp->assertStatus(200);
            $showPreviewResp->assertSee('Workshop Literasi Multimedia');
            $showPreviewResp->assertSee('Basis Data Lanjut');

            $parser = app(JadwalPerkuliahanParser::class);
            $parsed = $parser->parse($filePath);

            // Gagal validasi saat commit harus redirect kembali ke GET preview (bukan 405)
            $invalidCommitResp = $this->actingAs($this->plp)
                ->from(route('jadwal-lab.preview-show'))
                ->post(route('jadwal-lab.commit-import'), [
                    'nama_periode' => '',
                    'semester' => 'Ganjil',
                    'tahun_ajaran' => '2026/2027',
                    'entries_payload' => json_encode($parsed['entries']),
                ]);
            $invalidCommitResp->assertRedirect(route('jadwal-lab.preview-show'));
            $invalidCommitResp->assertSessionHasErrors('nama_periode');

            $commitResp = $this->actingAs($this->plp)->post(route('jadwal-lab.commit-import'), [
                'nama_periode' => $parsed['meta']['nama_periode'],
                'semester' => $parsed['meta']['semester'],
                'tahun_ajaran' => $parsed['meta']['tahun_ajaran'],
                'source_filename' => 'Jadwal Perkuliahan Ganjil 2026_2027.xlsx',
                'set_active' => '1',
                'entries_payload' => json_encode($parsed['entries']),
            ]);

            $periode = PeriodeJadwal::first();
            $this->assertNotNull($periode);
            $this->assertTrue($periode->is_active);
            $commitResp->assertRedirect(route('jadwal-lab.index', ['periode_id' => $periode->id]));

            $this->assertSame(count($parsed['entries']), JadwalLab::where('periode_jadwal_id', $periode->id)->count());

            // Validasi jam_ke_selesai < jam_ke_mulai ditolak
            $invalidJamResp = $this->actingAs($this->admin)->post(route('jadwal-lab.store'), [
                'periode_jadwal_id' => $periode->id,
                'lab_id' => $this->lab201->id,
                'nama_ruangan' => 'Lab L-201',
                'hari' => "Jum'at",
                'jam_ke_mulai' => 4,
                'jam_ke_selesai' => 2,
                'waktu_mulai' => '10.30',
                'waktu_berakhir' => '09.40',
                'mata_kuliah' => 'Jam Terbalik',
            ]);
            $invalidJamResp->assertSessionHasErrors('jam_ke_selesai');

            // Tambah manual pada hari Jum'at (menguji escape tanda petik pada atribut data-entry)
            $storeResp = $this->actingAs($this->admin)->post(route('jadwal-lab.store'), [
                'periode_jadwal_id' => $periode->id,
                'lab_id' => $this->lab201->id,
                'nama_ruangan' => 'Lab L-201',
                'hari' => "Jum'at",
                'jam_ke_mulai' => 1,
                'jam_ke_selesai' => 2,
                'waktu_mulai' => '08.00',
                'waktu_berakhir' => '09.40',
                'mata_kuliah' => 'Workshop Tambahan',
                'jenis' => 'Praktek',
                'semester' => '3',
                'program_studi' => 'TI',
                'dosen_id' => $this->dosenLusiana->id,
                'catatan' => 'Jadwal tambahan',
            ]);
            $storeResp->assertRedirect();
            $created = JadwalLab::where('mata_kuliah', 'Workshop Tambahan')->firstOrFail();

            $indexJumatResp = $this->actingAs($this->admin)->get(route('jadwal-lab.index', ['periode_id' => $periode->id]));
            $indexJumatResp->assertStatus(200);
            $indexJumatResp->assertSee('data-entry="', false);
            $indexJumatResp->assertDontSee("data-entry='", false);

            // Update manual
            $updateResp = $this->actingAs($this->plp)->put(route('jadwal-lab.update', $created->id), [
                'lab_id' => $this->lab201->id,
                'nama_ruangan' => 'Lab L-201',
                'hari' => 'Rabu',
                'jam_ke_mulai' => 1,
                'jam_ke_selesai' => 2,
                'waktu_mulai' => '08.00',
                'waktu_berakhir' => '09.40',
                'mata_kuliah' => 'Workshop Tambahan Revisi',
                'jenis' => 'Praktek',
                'semester' => '3',
                'program_studi' => 'TI',
                'dosen_id' => $this->dosenLusiana->id,
                'catatan' => 'Sudah diperbarui',
            ]);
            $updateResp->assertRedirect();
            $this->assertDatabaseHas('jadwal_labs', [
                'id' => $created->id,
                'mata_kuliah' => 'Workshop Tambahan Revisi',
            ]);

            // Hapus manual
            $deleteResp = $this->actingAs($this->plp)->delete(route('jadwal-lab.destroy', $created->id));
            $deleteResp->assertRedirect();
            $this->assertDatabaseMissing('jadwal_labs', ['id' => $created->id]);
        } finally {
            @unlink($filePath);
        }
    }

    public function test_role_permissions_and_pdf_excel_exports(): void
    {
        $periode = PeriodeJadwal::create([
            'nama_periode' => 'Semester Ganjil 2026/2027',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'is_active' => true,
            'uploaded_by' => $this->admin->id,
        ]);

        $jadwal = JadwalLab::create([
            'periode_jadwal_id' => $periode->id,
            'lab_id' => $this->lab201->id,
            'nama_ruangan' => 'Lab L-201',
            'hari' => 'Senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 2,
            'waktu_mulai' => '08.00',
            'waktu_berakhir' => '09.40',
            'mata_kuliah' => 'Basis Data Lanjut',
            'jenis' => 'Teori & Praktek',
            'kelas' => '3D3TI',
            'semester' => '3',
            'program_studi' => 'TI',
            'dosen_id' => $this->dosenLusiana->id,
            'nama_dosen' => 'Lusiana Agustien',
            'catatan' => null,
        ]);

        // Ruangan dengan catatan terisi harus menampilkan kolom Catatan (8 kolom)
        JadwalLab::create([
            'periode_jadwal_id' => $periode->id,
            'lab_id' => $this->lab203->id,
            'nama_ruangan' => 'Lab L-203',
            'hari' => 'Selasa',
            'jam_ke_mulai' => 3,
            'jam_ke_selesai' => 4,
            'waktu_mulai' => '09.40',
            'waktu_berakhir' => '11.20',
            'mata_kuliah' => 'Konsep Pemrograman',
            'jenis' => 'Praktek',
            'kelas' => '1D3TI',
            'semester' => '1',
            'program_studi' => 'TI',
            'dosen_id' => $this->dosenLusiana->id,
            'nama_dosen' => 'Lusiana Agustien',
            'catatan' => 'Bawa laptop',
        ]);

        // Mahasiswa & Dosen bisa lihat & export PDF/Excel
        foreach ([$this->mahasiswa, $this->dosenLusiana] as $viewer) {
            $this->actingAs($viewer)->get(route('jadwal-lab.index'))
                ->assertStatus(200)
                ->assertSee('Basis Data Lanjut');

            $pdfResp = $this->actingAs($viewer)->get(route('jadwal-lab.export-pdf', ['periode_id' => $periode->id]));
            $pdfResp->assertStatus(200);
            $this->assertStringContainsString('application/pdf', (string) $pdfResp->headers->get('content-type'));

            $excelResp = $this->actingAs($viewer)->get(route('jadwal-lab.export-excel', ['periode_id' => $periode->id]));
            $excelResp->assertStatus(200);
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                (string) $excelResp->headers->get('content-type')
            );

            // Verifikasi konten Excel yang di-generate valid dibaca kembali oleh SimpleXLSX
            $xlsx = SimpleXLSX::parseData($excelResp->getContent());
            $this->assertNotFalse($xlsx);
            $sheetNames = $xlsx->sheetNames();
            $this->assertContains('Lab L-201', $sheetNames);
            $this->assertContains('Lab L-203', $sheetNames);

            // Lab L-201 tanpa catatan: hanya 7 kolom (tanpa kolom Catatan)
            $idx201 = array_search('Lab L-201', $sheetNames, true);
            $rows201 = $xlsx->rows($idx201);
            $this->assertSame('Jadwal Penggunaan Lab L-201', $rows201[0][0]);
            $this->assertCount(7, $rows201[3]);
            $this->assertSame('Program Studi', $rows201[3][6]);
            $this->assertSame('Basis Data Lanjut', $rows201[4][1]);

            // Lab L-203 ada catatan: tetap 8 kolom dengan header & isi Catatan
            $idx203 = array_search('Lab L-203', $sheetNames, true);
            $rows203 = $xlsx->rows($idx203);
            $this->assertSame('Jadwal Penggunaan Lab L-203', $rows203[0][0]);
            $this->assertCount(8, $rows203[3]);
            $this->assertSame('Catatan', $rows203[3][7]);
            $this->assertSame('Bawa laptop', $rows203[5][7]);

            // Ditolak pada endpoint manajemen
            $this->actingAs($viewer)->post(route('jadwal-lab.store'), [])->assertStatus(403);
            $this->actingAs($viewer)->put(route('jadwal-lab.update', $jadwal->id), [])->assertStatus(403);
            $this->actingAs($viewer)->delete(route('jadwal-lab.destroy', $jadwal->id))->assertStatus(403);
            $this->actingAs($viewer)->patch(route('jadwal-lab.periode.activate', $periode->id))->assertStatus(403);
            $this->actingAs($viewer)->delete(route('jadwal-lab.periode.destroy', $periode->id))->assertStatus(403);
        }

        // PLP bisa hapus periode
        $this->actingAs($this->plp)->delete(route('jadwal-lab.periode.destroy', $periode->id))->assertRedirect();
        $this->assertDatabaseMissing('periode_jadwals', ['id' => $periode->id]);
    }
}

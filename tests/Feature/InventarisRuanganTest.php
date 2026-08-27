<?php

namespace Tests\Feature;

use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarisRuanganTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $plp;
    private $kalab;
    private $mahasiswa;
    private $lab;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['nama_role' => 'PLP', 'slug' => 'plp']);
        $roleKalab = DB::table('roles')->insertGetId(['nama_role' => 'Kalab', 'slug' => 'dosen']);
        $roleMahasiswa = DB::table('roles')->insertGetId(['nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Test',
            'nip' => '198501012010121001',
            'email' => 'plp@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->kalab = User::create([
            'role_id' => $roleKalab,
            'nama_asli' => 'Kalab Test',
            'nip' => '197501012000121002',
            'email' => 'kalab@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMahasiswa,
            'nama_asli' => 'Mhs Test',
            'nrp' => '3120500001',
            'email' => 'mhs@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->lab = Lab::create([
            'nama_lab' => 'Lab 201',
            'kode_lab' => 'L - 201',
            'lokasi' => 'Gedung A Lantai 2',
        ]);

        LabManager::create([
            'lab_id' => $this->lab->id,
            'plp_id' => $this->plp->id,
            'kalab_id' => $this->kalab->id,
        ]);
    }

    public function test_admin_and_plp_can_view_inventaris_page(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('inventaris-ruangan.index', ['lab_id' => $this->lab->id]));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Daftar Inventaris Ruangan (DIR)');
        $responseAdmin->assertSee('Lab 201');

        $responsePlp = $this->actingAs($this->plp)->get(route('inventaris-ruangan.index', ['lab_id' => $this->lab->id]));
        $responsePlp->assertStatus(200);
        $responsePlp->assertSee('Lab 201');
    }

    public function test_plp_can_create_inventaris_item(): void
    {
        $payload = [
            'lab_id' => $this->lab->id,
            'kode_barang' => '3050204004',
            'nama_barang' => 'A.C Split (Panasonik)',
            'spesifikasi_merk_tipe' => 'Panasonic',
            'tahun_perolehan' => 2012,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => 'rusak_berat',
            'nup' => '1',
            'is_bisa_dipinjam' => '1',
            'keterangan' => 'Kompresor rusak',
        ];

        $response = $this->actingAs($this->plp)->post(route('inventaris-ruangan.store'), $payload);
        $response->assertRedirect(route('inventaris-ruangan.index', ['lab_id' => $this->lab->id]));

        $this->assertDatabaseHas('inventaris_ruangans', [
            'lab_id' => $this->lab->id,
            'kode_barang' => '3050204004',
            'nama_barang' => 'A.C Split (Panasonik)',
            'kondisi' => 'rusak_berat',
            'is_bisa_dipinjam' => true,
        ]);
    }

    public function test_plp_can_update_inventaris_item(): void
    {
        $item = InventarisRuangan::create([
            'lab_id' => $this->lab->id,
            'kode_barang' => '3100102001',
            'nama_barang' => 'P.C Unit (Hp Pavilion)',
            'spesifikasi_merk_tipe' => 'HP',
            'tahun_perolehan' => 2012,
            'jumlah' => 24,
            'satuan' => 'Unit',
            'kondisi' => 'rusak_ringan',
        ]);

        $updatePayload = [
            'kode_barang' => '3100102001',
            'nama_barang' => 'P.C Unit (Hp Pavilion)',
            'spesifikasi_merk_tipe' => 'HP Core i5',
            'tahun_perolehan' => 2012,
            'jumlah' => 24,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
            'keterangan' => 'Sudah diperbaiki PLP',
        ];

        $response = $this->actingAs($this->plp)->put(route('inventaris-ruangan.update', $item->id), $updatePayload);
        $response->assertRedirect(route('inventaris-ruangan.index', ['lab_id' => $this->lab->id]));

        $this->assertDatabaseHas('inventaris_ruangans', [
            'id' => $item->id,
            'kondisi' => 'baik',
            'spesifikasi_merk_tipe' => 'HP Core i5',
        ]);
    }

    public function test_plp_can_delete_inventaris_item(): void
    {
        $item = InventarisRuangan::create([
            'lab_id' => $this->lab->id,
            'nama_barang' => 'Head set',
            'spesifikasi_merk_tipe' => 'Senheiser',
            'tahun_perolehan' => 2013,
            'jumlah' => 25,
            'satuan' => 'Unit',
            'kondisi' => 'rusak_ringan',
        ]);

        $response = $this->actingAs($this->plp)->delete(route('inventaris-ruangan.destroy', $item->id));
        $response->assertRedirect(route('inventaris-ruangan.index', ['lab_id' => $this->lab->id]));

        $this->assertDatabaseMissing('inventaris_ruangans', [
            'id' => $item->id,
        ]);
    }

    public function test_can_export_pdf_dir(): void
    {
        InventarisRuangan::create([
            'lab_id' => $this->lab->id,
            'kode_barang' => '3050105058',
            'nama_barang' => 'Layar Film/Proyektor',
            'spesifikasi_merk_tipe' => 'Gic',
            'tahun_perolehan' => 2012,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
        ]);

        $response = $this->actingAs($this->plp)->get(route('inventaris-ruangan.export-pdf', $this->lab->id));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_mahasiswa_cannot_access_inventaris_crud(): void
    {
        $response = $this->actingAs($this->mahasiswa)->get(route('inventaris-ruangan.index'));
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\InventarisBarang;
use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarisBarangTest extends TestCase
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
            'email' => 'admin_master@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Test',
            'nip' => '198501012010121001',
            'email' => 'plp_master@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->kalab = User::create([
            'role_id' => $roleKalab,
            'nama_asli' => 'Kalab Test',
            'nip' => '197501012000121002',
            'email' => 'kalab_master@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMahasiswa,
            'nama_asli' => 'Mhs Test',
            'nrp' => '3120500001',
            'email' => 'mhs_master@test.com',
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

    public function test_admin_and_plp_can_view_master_inventaris_page(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('inventaris.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Master Data Inventaris');

        $responsePlp = $this->actingAs($this->plp)->get(route('inventaris.index'));
        $responsePlp->assertStatus(200);
        $responsePlp->assertSee('Master Data Inventaris');
    }

    public function test_admin_or_plp_can_create_master_inventaris(): void
    {
        $payload = [
            'kode_barang' => '3030101033',
            'nup' => '1',
            'nama_barang' => 'Personal Computer Dell',
            'merk' => 'Dell',
            'tipe' => 'OptiPlex 7080',
            'tgl_buku_pertama' => '2023-01-15',
            'tgl_perolehan' => '2023-01-10',
            'spesifikasi' => 'Intel Core i7 16GB RAM SSD 512GB',
            'keterangan' => 'Pengadaan Aset Kampus 2023',
        ];

        $response = $this->actingAs($this->plp)->post(route('inventaris.store'), $payload);
        $response->assertRedirect(route('inventaris.index'));

        $this->assertDatabaseHas('inventaris_barangs', [
            'kode_barang' => '3030101033',
            'nama_barang' => 'Personal Computer Dell',
            'merk' => 'Dell',
            'tipe' => 'OptiPlex 7080',
        ]);
    }

    public function test_admin_or_plp_can_update_master_inventaris(): void
    {
        $barang = InventarisBarang::create([
            'kode_barang' => '3030101033',
            'nama_barang' => 'Osiloskop Digital',
            'merk' => 'Rigol',
            'tipe' => 'DS1054Z',
        ]);

        $updatePayload = [
            'kode_barang' => '3030101033',
            'nama_barang' => 'Osiloskop Digital 4 Channel',
            'merk' => 'Rigol',
            'tipe' => 'DS1054Z Plus',
            'keterangan' => 'Sudah dikalibrasi',
        ];

        $response = $this->actingAs($this->admin)->put(route('inventaris.update', $barang->id), $updatePayload);
        $response->assertRedirect(route('inventaris.index'));

        $this->assertDatabaseHas('inventaris_barangs', [
            'id' => $barang->id,
            'nama_barang' => 'Osiloskop Digital 4 Channel',
            'tipe' => 'DS1054Z Plus',
        ]);
    }

    public function test_admin_or_plp_can_delete_master_inventaris(): void
    {
        $barang = InventarisBarang::create([
            'nama_barang' => 'Barang Salah Input',
        ]);

        $response = $this->actingAs($this->plp)->delete(route('inventaris.destroy', $barang->id));
        $response->assertRedirect(route('inventaris.index'));

        $this->assertDatabaseMissing('inventaris_barangs', [
            'id' => $barang->id,
        ]);
    }

    public function test_admin_or_plp_can_assign_master_inventaris_to_ruangan(): void
    {
        $barang = InventarisBarang::create([
            'kode_barang' => '3030101050',
            'nama_barang' => 'Switch Cisco Catalyst',
            'merk' => 'Cisco',
            'tipe' => '2960',
            'tgl_perolehan' => '2022-05-10',
        ]);

        $assignPayload = [
            'lab_id' => $this->lab->id,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => 'baik',
            'is_bisa_dipinjam' => '1',
            'keterangan' => 'Terpasang di Rak Server 1',
        ];

        $response = $this->actingAs($this->plp)->post(route('inventaris.assign', $barang->id), $assignPayload);
        $response->assertRedirect(route('inventaris.index'));

        $this->assertDatabaseHas('inventaris_ruangans', [
            'inventaris_barang_id' => $barang->id,
            'lab_id' => $this->lab->id,
            'nama_barang' => 'Switch Cisco Catalyst',
            'spesifikasi_merk_tipe' => 'Cisco - 2960',
            'tahun_perolehan' => 2022,
            'jumlah' => 1,
            'kondisi' => 'baik',
            'is_bisa_dipinjam' => true,
        ]);

        $this->assertTrue($barang->fresh()->is_assigned);
    }

    public function test_admin_or_plp_can_download_excel_template(): void
    {
        $response = $this->actingAs($this->plp)->get(route('inventaris.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_master_inventaris.csv"');
    }

    public function test_admin_or_plp_can_import_data(): void
    {
        $csvHeader = "Kode Barang,NUP,Nama Barang,Merk,Tipe,Tanggal Buku Pertama,Tanggal Perolehan\n";
        $csvRow1 = "2010104002,1,Tanah Bangunan Pendidikan Dan Latihan,,,2021-12-31,2004-03-03\n";
        $csvRow2 = "3030101033,1,Mesin Laser Cutting,CUTTING STICKER JINKA PRO 1351,CUTTING STICKER JINKA PRO 1351,2021-12-31,2018-05-21\n";
        $csvContent = $csvHeader . $csvRow1 . $csvRow2;

        $file = UploadedFile::fake()->createWithContent('inventaris_import.csv', $csvContent);

        $response = $this->actingAs($this->plp)->post(route('inventaris.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('inventaris.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventaris_barangs', [
            'kode_barang' => '2010104002',
            'nup' => '1',
            'nama_barang' => 'Tanah Bangunan Pendidikan Dan Latihan',
        ]);

        $this->assertDatabaseHas('inventaris_barangs', [
            'kode_barang' => '3030101033',
            'nup' => '1',
            'nama_barang' => 'Mesin Laser Cutting',
            'merk' => 'CUTTING STICKER JINKA PRO 1351',
            'tipe' => 'CUTTING STICKER JINKA PRO 1351',
        ]);
    }

    public function test_kalab_and_mahasiswa_cannot_modify_master_inventaris(): void
    {
        $responseKalab = $this->actingAs($this->kalab)->get(route('inventaris.index'));
        $responseKalab->assertStatus(403);

        $responseMhs = $this->actingAs($this->mahasiswa)->get(route('inventaris.index'));
        $responseMhs->assertStatus(403);
    }
}

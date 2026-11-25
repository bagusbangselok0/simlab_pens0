<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JabatanCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $dosen;
    private int $roleDosenId;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdminId = DB::table('roles')->insertGetId(['nama_role' => 'Admin', 'slug' => 'admin']);
        $this->roleDosenId = DB::table('roles')->insertGetId(['nama_role' => 'Dosen', 'slug' => 'dosen']);

        $this->admin = User::create([
            'role_id' => $roleAdminId,
            'nama_asli' => 'Admin SIMLAB',
            'email' => 'admin-jabatan@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->dosen = User::create([
            'role_id' => $this->roleDosenId,
            'nama_asli' => 'Dosen Biasa',
            'email' => 'dosen-jabatan@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_jabatan_routes(): void
    {
        $this->actingAs($this->dosen)
            ->get(route('jabatan.index'))
            ->assertStatus(403);

        $this->actingAs($this->dosen)
            ->postJson(route('jabatan.store'), [
                'nama_jabatan' => 'Sekretaris Jurusan',
                'slug' => 'sekjur',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_view_jabatan_index_and_datatables_json(): void
    {
        Jabatan::create([
            'nama_jabatan' => 'Kepala Laboratorium',
            'slug' => 'kalab',
        ]);

        $this->actingAs($this->admin)
            ->get(route('jabatan.index'))
            ->assertStatus(200)
            ->assertSee('Daftar Jabatan');

        $this->actingAs($this->admin)
            ->getJson(route('jabatan.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_edit_update_and_validate_duplicates(): void
    {
        // Create dengan slug kosong -> auto-generate dari nama_jabatan
        $this->actingAs($this->admin)
            ->postJson(route('jabatan.store'), [
                'nama_jabatan' => 'Kepala Unit Lab',
                'slug' => '',
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_jabatan' => 'Kepala Unit Lab',
                    'slug' => 'kepala-unit-lab',
                ],
            ]);

        $jabatan = Jabatan::where('slug', 'kepala-unit-lab')->firstOrFail();

        // Duplikat nama_jabatan atau slug ditolak 422
        $this->actingAs($this->admin)
            ->postJson(route('jabatan.store'), [
                'nama_jabatan' => 'Kepala Unit Lab',
                'slug' => 'slug-baru',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_jabatan']);

        $this->actingAs($this->admin)
            ->postJson(route('jabatan.store'), [
                'nama_jabatan' => 'Nama Lain',
                'slug' => 'kepala-unit-lab',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);

        // Edit detail
        $this->actingAs($this->admin)
            ->getJson(route('jabatan.edit', $jabatan->id))
            ->assertStatus(200)
            ->assertJson([
                'id' => $jabatan->id,
                'nama_jabatan' => 'Kepala Unit Lab',
                'slug' => 'kepala-unit-lab',
            ]);

        // Update berhasil
        $this->actingAs($this->admin)
            ->putJson(route('jabatan.update', $jabatan->id), [
                'nama_jabatan' => 'Kepala Lab Terpadu',
                'slug' => 'kalab-terpadu',
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_jabatan' => 'Kepala Lab Terpadu',
                    'slug' => 'kalab-terpadu',
                ],
            ]);

        $this->assertDatabaseHas('jabatans', [
            'id' => $jabatan->id,
            'nama_jabatan' => 'Kepala Lab Terpadu',
            'slug' => 'kalab-terpadu',
        ]);
    }

    public function test_admin_cannot_delete_used_jabatan_but_can_delete_unused_jabatan(): void
    {
        $usedJabatan = Jabatan::create([
            'nama_jabatan' => 'Kepala Program Studi',
            'slug' => 'kaprodi',
        ]);

        User::create([
            'role_id' => $this->roleDosenId,
            'jabatan_id' => $usedJabatan->id,
            'nama_asli' => 'Kaprodi Test',
            'email' => 'kaprodi-jabatan@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        // Ditolak 422 karena masih dipakai user
        $this->actingAs($this->admin)
            ->deleteJson(route('jabatan.destroy', $usedJabatan->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('jabatans', ['id' => $usedJabatan->id]);

        // Jabatan yang tidak dipakai user bisa dihapus
        $unusedJabatan = Jabatan::create([
            'nama_jabatan' => 'Jabatan Sementara',
            'slug' => 'jabatan-sementara',
        ]);

        $this->actingAs($this->admin)
            ->deleteJson(route('jabatan.destroy', $unusedJabatan->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('jabatans', ['id' => $unusedJabatan->id]);
    }
}

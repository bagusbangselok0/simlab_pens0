<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\BreadcrumbResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $plp;
    private User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId(['id' => 1, 'nama_role' => 'Admin', 'slug' => 'admin']);
        $rolePlp = DB::table('roles')->insertGetId(['id' => 3, 'nama_role' => 'PLP', 'slug' => 'plp']);
        $roleMhs = DB::table('roles')->insertGetId(['id' => 2, 'nama_role' => 'Mahasiswa', 'slug' => 'mahasiswa']);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Breadcrumb',
            'email' => 'admin-bc@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->plp = User::create([
            'role_id' => $rolePlp,
            'nama_asli' => 'PLP Breadcrumb',
            'nip' => '198601012011121001',
            'email' => 'plp-bc@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->mahasiswa = User::create([
            'role_id' => $roleMhs,
            'nama_asli' => 'Mahasiswa Breadcrumb',
            'nrp' => '3125500099',
            'email' => 'mhs-bc@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    public function test_dashboard_hides_breadcrumb(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('class="simlab-breadcrumb-nav', false);
    }

    public function test_settings_and_jadwal_lab_render_hierarchy_breadcrumbs(): void
    {
        $settingsResp = $this->actingAs($this->admin)->get(route('settings.index'));
        $settingsResp->assertStatus(200);
        $settingsResp->assertSee('class="simlab-breadcrumb-nav', false);
        $settingsResp->assertSee('Data Master');
        $settingsResp->assertSee('Pengaturan Sistem');

        $jadwalResp = $this->actingAs($this->mahasiswa)->get(route('jadwal-lab.index'));
        $jadwalResp->assertStatus(200);
        $jadwalResp->assertSee('class="simlab-breadcrumb-nav', false);
        $jadwalResp->assertSee('Jadwal Lab');
    }

    public function test_resolver_adapts_parent_link_by_role_for_peminjaman_alat_detail(): void
    {
        $plpCrumbs = BreadcrumbResolver::resolve(null, [
            'route_name' => 'peminjaman-alat.show',
            'title' => 'Detail Peminjaman #12',
            'user' => $this->plp,
        ]);

        $this->assertCount(3, $plpCrumbs);
        $this->assertSame('Persetujuan Alat', $plpCrumbs[1]['label']);
        $this->assertSame(route('peminjaman-alat.approval'), $plpCrumbs[1]['url']);
        $this->assertSame('Detail Peminjaman #12', $plpCrumbs[2]['label']);
        $this->assertNull($plpCrumbs[2]['url']);

        $mhsCrumbs = BreadcrumbResolver::resolve(null, [
            'route_name' => 'peminjaman-alat.show',
            'title' => 'Detail Peminjaman #12',
            'user' => $this->mahasiswa,
        ]);

        $this->assertCount(3, $mhsCrumbs);
        $this->assertSame('Peminjaman Alat', $mhsCrumbs[1]['label']);
        $this->assertSame(route('peminjaman-alat.index'), $mhsCrumbs[1]['url']);
    }
}

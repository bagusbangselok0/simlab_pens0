<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SidebarResponsiveTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = DB::table('roles')->insertGetId([
            'id' => 1,
            'nama_role' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->admin = User::create([
            'role_id' => $roleAdmin,
            'nama_asli' => 'Admin Test',
            'email' => 'admin-sidebar@test.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    public function test_sidebar_does_not_render_active_class_on_initial_html_when_desktop_default_is_expanded(): void
    {
        Setting::updateOrCreate(
            [
                'setting_key' => Setting::THEME_SIDEBAR_DEFAULT,
                'scope_key' => 'global',
            ],
            [
                'value' => 'expanded',
                'value_type' => 'string',
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $response->assertSee('<div id="sidebar" class="" data-desktop-default="expanded">', false);
        $response->assertDontSee('<div id="sidebar" class="active"', false);
        $response->assertSee('class="burger-btn d-block d-xl-none"', false);
    }

    public function test_sidebar_renders_inactive_and_visible_burger_when_desktop_default_is_collapsed(): void
    {
        Setting::updateOrCreate(
            [
                'setting_key' => Setting::THEME_SIDEBAR_DEFAULT,
                'scope_key' => 'global',
            ],
            [
                'value' => 'collapsed',
                'value_type' => 'string',
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $response->assertSee('<div id="sidebar" class="inactive" data-desktop-default="collapsed">', false);
        $response->assertSee('class="burger-btn d-block "', false);
    }
}

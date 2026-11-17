<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_tombol_panel_admin_tanpa_kelola_lapangan_di_navbar(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertSee('tombol-panel-admin', false)
            ->assertDontSee('Kelola Lapangan');
    }

    public function test_pelanggan_dan_tamu_tidak_melihat_tombol_panel_admin(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('tombol-panel-admin', false);

        $this->actingAs(User::factory()->create())->get(route('home'))
            ->assertOk()
            ->assertDontSee('tombol-panel-admin', false);
    }
}

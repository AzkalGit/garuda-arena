<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function buatAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $admin;
    }

    public function test_halaman_admin_memakai_sidebar_dengan_menu_admin(): void
    {
        $response = $this->actingAs($this->buatAdmin())->get(route('admin.bookings.index'));

        $response->assertOk();
        $response->assertSee('admin-sisi', false);
        $response->assertSee('Kelola Booking');
        $response->assertSee('Kelola Lapangan');
        $response->assertSee('Keluar');
    }

    public function test_halaman_kelola_lapangan_juga_memakai_sidebar(): void
    {
        $response = $this->actingAs($this->buatAdmin())->get(route('admin.fields.index'));

        $response->assertOk();
        $response->assertSee('admin-sisi', false);
    }

    public function test_halaman_publik_tidak_memakai_sidebar_admin(): void
    {
        $response = $this->actingAs($this->buatAdmin())->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('admin-sisi', false);
    }
}
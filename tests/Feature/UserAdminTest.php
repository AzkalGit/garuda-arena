<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private function buatAdmin(): User
    {
        $admin = User::factory()->create(['name' => 'Admin Utama', 'email' => 'admin@contoh.test']);
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $admin;
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/pengguna')->assertRedirect('/login');
    }

    public function test_pelanggan_mendapat_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/pengguna')
            ->assertForbidden();
    }

    public function test_admin_melihat_semua_pengguna_dan_peran(): void
    {
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@contoh.test']);

        $this->actingAs($this->buatAdmin())
            ->get('/admin/pengguna')
            ->assertOk()
            ->assertSee('Kelola Pengguna')
            ->assertSee('Admin Utama')
            ->assertSee('Budi Santoso')
            ->assertSee('Pelanggan')
            ->assertSee('2 pengguna');
    }

    public function test_pencarian_menyaring_nama_dan_email(): void
    {
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@contoh.test']);
        User::factory()->create(['name' => 'Citra Dewi', 'email' => 'citra@contoh.test']);

        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->get('/admin/pengguna?q=Budi')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Citra Dewi');

        $this->actingAs($admin)
            ->get('/admin/pengguna?q=citra@')
            ->assertOk()
            ->assertSee('Citra Dewi')
            ->assertDontSee('Budi Santoso');
    }

    public function test_pencarian_tanpa_hasil_menampilkan_pesan_kosong(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin/pengguna?q=tidakada')
            ->assertOk()
            ->assertSee('Tidak ada pengguna yang cocok');
    }

    public function test_jumlah_booking_tersedia_dan_menu_sidebar_muncul(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin/pengguna')
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->first()->bookings_count === 0)
            ->assertSee(route('admin.users.index'));
    }
}

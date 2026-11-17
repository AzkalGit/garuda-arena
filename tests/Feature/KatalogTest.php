<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_beranda_tampil_untuk_tamu_dengan_lapangan_unggulan(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Garuda Arena VIP')
            ->assertSee('Merdeka Arena')
            ->assertSee('Champions Court');
    }

    public function test_beranda_hanya_menampilkan_lapangan_aktif(): void
    {
        Field::where('name', 'Garuda Arena VIP')->update(['status' => Field::STATUS_NONAKTIF]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Garuda Arena VIP')
            ->assertSee('Rencong Arena');
    }

    public function test_daftar_menampilkan_semua_lapangan_termasuk_perawatan(): void
    {
        $this->get('/lapangan')
            ->assertOk()
            ->assertSee('Menampilkan 8 dari 8 lapangan')
            ->assertSee('Lampuuk Turf')
            ->assertSee('Perawatan')
            ->assertSee('Tidak Tersedia');
    }

    public function test_filter_jenis_lantai(): void
    {
        $this->get('/lapangan?jenis=vinyl')
            ->assertOk()
            ->assertSee('Menampilkan 2 dari 8 lapangan')
            ->assertSee('Champions Court')
            ->assertSee('Peunayong Arena')
            ->assertDontSee('Merdeka Arena');
    }

    public function test_filter_rentang_harga(): void
    {
        $this->get('/lapangan?harga=under150')->assertSee('Menampilkan 3 dari 8 lapangan');
        $this->get('/lapangan?harga=150to200')->assertSee('Menampilkan 4 dari 8 lapangan');
        $this->get('/lapangan?harga=above200')->assertSee('Menampilkan 1 dari 8 lapangan');
    }

    public function test_urutan_harga(): void
    {
        $this->get('/lapangan?urut=priceAsc')
            ->assertOk()
            ->assertSeeInOrder(['Krueng Arena', 'Seulawah Court']);

        $this->get('/lapangan?urut=priceDesc')
            ->assertOk()
            ->assertSeeInOrder(['Peunayong Arena', 'Champions Court']);
    }

    public function test_parameter_tidak_valid_diabaikan(): void
    {
        $this->get('/lapangan?jenis=ngawur&harga[]=x&urut=zzz')
            ->assertOk()
            ->assertSee('Menampilkan 8 dari 8 lapangan');
    }

    public function test_navigasi_tamu_menampilkan_masuk_dan_daftar(): void
    {
        $this->get('/')
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertDontSee('Keluar');
    }

    public function test_navigasi_pelanggan_menampilkan_menu_pengguna(): void
    {
        $andi = User::where('email', 'andi@garudaarena.test')->firstOrFail();

        $this->actingAs($andi)->get('/')
            ->assertOk()
            ->assertSee('Keluar')
            ->assertSee('data-peran="pelanggan"', false);
    }

    public function test_navigasi_admin_menandai_peran_admin(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)->get('/')
            ->assertOk()
            ->assertSee('data-peran="admin"', false);
    }

    public function test_halaman_dashboard_bawaan_sudah_tidak_ada(): void
    {
        $andi = User::where('email', 'andi@garudaarena.test')->firstOrFail();

        $this->actingAs($andi)->get('/dashboard')->assertNotFound();
    }
}

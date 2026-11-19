<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_tamu_bisa_melihat_galeri_dengan_nama_dan_lokasi(): void
    {
        $field = Field::where('status', Field::STATUS_AKTIF)->first();

        $this->get('/galeri')
            ->assertOk()
            ->assertSee('Galeri Lapangan')
            ->assertSee($field->name)
            ->assertSee($field->location)
            ->assertSee('css/galeri.css');
    }

    public function test_admin_juga_bisa_melihat_galeri(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->actingAs($admin)->get('/galeri')->assertOk();
    }

    public function test_lapangan_nonaktif_tidak_tampil_di_galeri(): void
    {
        Field::where('name', 'Garuda Arena VIP')->update(['status' => Field::STATUS_NONAKTIF]);

        $this->get('/galeri')
            ->assertOk()
            ->assertDontSee('Garuda Arena VIP')
            ->assertSee('Rencong Arena');
    }

    public function test_filter_jenis_hanya_menampilkan_jenis_itu(): void
    {
        $vinyl = Field::where('type', Field::TYPE_VINYL)->where('status', Field::STATUS_AKTIF)->first();
        $interlock = Field::where('type', Field::TYPE_INTERLOCK)->where('status', Field::STATUS_AKTIF)->first();

        $this->get('/galeri?jenis=vinyl')
            ->assertOk()
            ->assertSee($vinyl->name)
            ->assertDontSee($interlock->name);
    }

    public function test_jenis_tak_dikenal_diabaikan(): void
    {
        $vinyl = Field::where('type', Field::TYPE_VINYL)->where('status', Field::STATUS_AKTIF)->first();
        $interlock = Field::where('type', Field::TYPE_INTERLOCK)->where('status', Field::STATUS_AKTIF)->first();

        $this->get('/galeri?jenis=lantai-kayu')
            ->assertOk()
            ->assertSee($vinyl->name)
            ->assertSee($interlock->name);

        $this->get('/galeri?jenis[]=vinyl')
            ->assertOk()
            ->assertSee($interlock->name);
    }

    public function test_navbar_memuat_tautan_galeri(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('galeri.index'));
    }
}

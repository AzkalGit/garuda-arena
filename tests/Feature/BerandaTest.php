<?php

namespace Tests\Feature;

use App\Models\Field;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_kartu_cek_cepat_hanya_memuat_lapangan_aktif(): void
    {
        $aktif = Field::where('status', Field::STATUS_AKTIF)->first();
        $nonaktif = Field::where('status', '!=', Field::STATUS_AKTIF)->first();

        $this->get('/')
            ->assertOk()
            ->assertSee('Cek Ketersediaan Cepat')
            ->assertSee('<option value="'.$aktif->id.'"', false)
            ->assertDontSee('<option value="'.$nonaktif->id.'"', false);
    }

    public function test_cek_cepat_mengarah_ke_detail_lapangan_dengan_tanggal(): void
    {
        $field = Field::where('status', Field::STATUS_AKTIF)->first();

        $this->get('/?lapangan='.$field->id.'&tanggal=2026-11-25')
            ->assertRedirect(route('lapangan.show', ['field' => $field, 'tanggal' => '2026-11-25']));
    }

    public function test_tanggal_tidak_valid_diabaikan(): void
    {
        $field = Field::where('status', Field::STATUS_AKTIF)->first();

        $this->get('/?lapangan='.$field->id.'&tanggal=besok')
            ->assertRedirect(route('lapangan.show', ['field' => $field]));
    }

    public function test_semua_lapangan_mengarah_ke_daftar(): void
    {
        $this->get('/?lapangan=semua&tanggal=2026-11-25')
            ->assertRedirect(route('lapangan.index'));
    }

    public function test_lapangan_nonaktif_atau_tak_dikenal_tetap_di_beranda(): void
    {
        $nonaktif = Field::where('status', '!=', Field::STATUS_AKTIF)->first();

        $this->get('/?lapangan='.$nonaktif->id)->assertOk();
        $this->get('/?lapangan=abc')->assertOk();
        $this->get('/?lapangan[]=1')->assertOk();
    }

    public function test_tombol_lihat_selengkapnya_dan_logo_baru_tampil(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lihat Selengkapnya')
            ->assertSee('FUTSAL &amp; SPORT', false)
            ->assertSee('logo-kotak', false);
    }
}

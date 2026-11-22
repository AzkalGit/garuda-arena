<?php

namespace Tests\Feature;

use App\Models\Field;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaHeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_kartu_hero_memakai_lapangan_aktif_dengan_ulasan_terbanyak(): void
    {
        $hero = Field::where('status', Field::STATUS_AKTIF)
            ->orderByDesc('reviews_count')
            ->orderBy('id')
            ->first();

        $this->get('/')
            ->assertOk()
            ->assertViewHas('hero', fn ($field) => $field->is($hero))
            ->assertSee('hero-kartu', false)
            ->assertSee('hero-lencana-rating', false)
            ->assertSeeText($hero->name)
            ->assertDontSee('Competition Ready');
    }

    public function test_lapangan_nonaktif_tidak_pernah_jadi_hero(): void
    {
        Field::where('status', '!=', Field::STATUS_AKTIF)->update(['reviews_count' => 999]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('hero', fn ($field) => $field->status === Field::STATUS_AKTIF);
    }

    public function test_kartu_keunggulan_punya_empat_ikon(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(4, substr_count($html, 'class="ikon-keunggulan"'));
    }

    public function test_galeri_beranda_punya_tombol_ke_halaman_galeri(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lihat Galeri Lengkap')
            ->assertSee('seksi-galeri', false);
    }
}

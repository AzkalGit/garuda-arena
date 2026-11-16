<?php

namespace Tests\Feature;

use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailLapanganTest extends TestCase
{
    use RefreshDatabase;

    private function buatLapangan(string $status = Field::STATUS_AKTIF, string $nama = 'Garuda Arena VIP'): Field
    {
        return Field::create([
            'name' => $nama,
            'type' => Field::TYPE_INTERLOCK,
            'price_per_hour' => 180000,
            'location' => 'Arena Utama Lantai 1',
            'description' => 'Lapangan interlock dengan pencahayaan terbaik.',
            'size' => '25 x 15 m',
            'capacity' => 10,
            'facilities' => ['Ruang Ganti', 'Kantin'],
            'image' => 'images/2-venue-interlock.jpg',
            'rating' => 4.6,
            'reviews_count' => 16,
            'status' => $status,
        ]);
    }

    public function test_tamu_bisa_melihat_detail_lapangan_aktif(): void
    {
        $field = $this->buatLapangan();

        $this->get(route('lapangan.show', $field))
            ->assertOk()
            ->assertSee('Garuda Arena VIP')
            ->assertSee('Rp 180.000')
            ->assertSee('Ruang Ganti')
            ->assertSee('Kantin')
            ->assertSee('Lapangan interlock dengan pencahayaan terbaik.')
            ->assertSee('08:00 – 23:00');
    }

    public function test_detail_lapangan_nonaktif_mengembalikan_404(): void
    {
        $field = $this->buatLapangan(Field::STATUS_NONAKTIF, 'Lampuuk Turf');

        $this->get(route('lapangan.show', $field))->assertNotFound();
    }

    public function test_detail_lapangan_yang_tidak_ada_mengembalikan_404(): void
    {
        $this->get('/lapangan/99999')->assertNotFound();
    }

    public function test_kartu_lapangan_aktif_menautkan_ke_halaman_detail(): void
    {
        $field = $this->buatLapangan();

        $this->get(route('lapangan.index'))
            ->assertOk()
            ->assertSee(route('lapangan.show', $field), false);
    }

    public function test_kartu_lapangan_nonaktif_tanpa_tautan_detail(): void
    {
        $field = $this->buatLapangan(Field::STATUS_NONAKTIF, 'Lampuuk Turf');

        $this->get(route('lapangan.index'))
            ->assertOk()
            ->assertSee('Lampuuk Turf')
            ->assertDontSee(route('lapangan.show', $field), false);
    }
}

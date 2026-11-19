<?php

namespace Tests\Feature;

use App\Models\Field;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IkonFasilitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_kata_kunci_dipetakan_ke_ikon_yang_tepat(): void
    {
        $this->blade('<x-ikon-fasilitas nama="Parkir Luas" />')->assertSee('data-ikon="parkir"', false);
        $this->blade('<x-ikon-fasilitas nama="Free Wi-Fi" />')->assertSee('data-ikon="wifi"', false);
        $this->blade('<x-ikon-fasilitas nama="AC Central" />')->assertSee('data-ikon="ac"', false);
        $this->blade('<x-ikon-fasilitas nama="Kantin & Cafe" />')->assertSee('data-ikon="kantin"', false);
        $this->blade('<x-ikon-fasilitas nama="Live Streaming Ready" />')->assertSee('data-ikon="streaming"', false);
        $this->blade('<x-ikon-fasilitas nama="Pencahayaan Malam" />')->assertSee('data-ikon="cahaya"', false);
    }

    public function test_fasilitas_tak_dikenal_memakai_ikon_centang(): void
    {
        $this->blade('<x-ikon-fasilitas nama="Fasilitas Aneh" />')->assertSee('data-ikon="centang"', false);
    }

    public function test_katalog_menampilkan_ikon_fasilitas(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('lapangan.index'))
            ->assertOk()
            ->assertSee('class="ikon-fasilitas"', false);
    }

    public function test_detail_lapangan_menampilkan_ikon_fasilitas(): void
    {
        $this->seed(DatabaseSeeder::class);
        $field = Field::where('status', Field::STATUS_AKTIF)->firstOrFail();

        $this->get(route('lapangan.show', $field))
            ->assertOk()
            ->assertSee('class="ikon-fasilitas"', false);
    }

    public function test_komponen_dan_view_bebas_gaya_terlarang(): void
    {
        foreach ([
            'resources/views/components/ikon-fasilitas.blade.php',
            'resources/views/components/field-card.blade.php',
            'resources/views/fields/show.blade.php',
        ] as $berkas) {
            $isi = file_get_contents(base_path($berkas));

            $this->assertSame(
                0,
                preg_match('/class="[^"]*(bg-|text-|px-|py-|rounded|border-gray|flex|grid-cols|space-)|style=|<style/', $isi),
                "Gaya terlarang di {$berkas}"
            );
        }
    }
}

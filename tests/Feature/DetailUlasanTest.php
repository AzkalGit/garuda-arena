<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DetailUlasanTest extends TestCase
{
    use RefreshDatabase;

    private Field $lapangan;

    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));

        $this->lapangan = Field::create([
            'name' => 'Garuda Arena VIP',
            'type' => Field::TYPE_INTERLOCK,
            'price_per_hour' => 180000,
            'location' => 'Arena Utama Lantai 1',
            'description' => 'Lapangan uji.',
            'size' => '25 x 15 m',
            'capacity' => '10 pemain',
            'facilities' => ['Kantin'],
            'image' => 'images/2-venue-interlock.jpg',
            'rating' => 4.0,
            'reviews_count' => 1,
            'status' => Field::STATUS_AKTIF,
        ]);
    }

    private function buatUlasan(string $nama, int $rating, string $komentar): Review
    {
        $this->urut++;

        $user = User::factory()->create(['name' => $nama]);

        $booking = Booking::create([
            'code' => 'GA-2011-V'.$this->urut,
            'user_id' => $user->id,
            'field_id' => $this->lapangan->id,
            'date' => '2026-11-19',
            'start_hour' => 19,
            'duration' => 2,
            'price_per_hour' => $this->lapangan->price_per_hour,
            'total' => $this->lapangan->price_per_hour * 2,
            'status' => Booking::STATUS_SELESAI,
        ]);

        return Review::create([
            'user_id' => $user->id,
            'field_id' => $this->lapangan->id,
            'booking_id' => $booking->id,
            'rating' => $rating,
            'comment' => $komentar,
        ]);
    }

    public function test_detail_lapangan_menampilkan_ulasan_pelanggan(): void
    {
        $this->buatUlasan('Rina Kusuma', 4, 'Lapangannya nyaman dan bersih.');

        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('css/ulasan.css', false)
            ->assertSee('Ulasan Pelanggan')
            ->assertSee('Rina Kusuma')
            ->assertSee('Lapangannya nyaman dan bersih.')
            ->assertSee('★★★★☆');
    }

    public function test_detail_lapangan_tanpa_ulasan_menampilkan_pesan_kosong(): void
    {
        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Belum ada ulasan untuk lapangan ini.');
    }

    public function test_komentar_ulasan_di_detail_di_escape(): void
    {
        $this->buatUlasan('Tono', 3, '<script>alert(1)</script> lumayan');

        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('lumayan');
    }

    public function test_seeder_membuat_ulasan_dan_menyisakan_booking_selesai_untuk_demo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Review::count());

        $demo = Booking::where('code', 'like', '%-L2M2')->firstOrFail();
        $this->assertSame(Booking::STATUS_SELESAI, $demo->status);
        $this->assertTrue($demo->bisaDiulas());
    }
}

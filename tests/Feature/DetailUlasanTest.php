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

    private function buatBanyakUlasan(int $jumlah): void
    {
        for ($i = 1; $i <= $jumlah; $i++) {
            $this->buatUlasan('Penulis '.$i, 4, 'Komentar nomor '.$i.' untuk lapangan ini.');
        }
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
            ->assertSee('Belum ada ulasan untuk lapangan ini.')
            ->assertDontSee('Lihat selengkapnya');
    }

    public function test_komentar_ulasan_di_detail_di_escape(): void
    {
        $this->buatUlasan('Tono', 3, '<script>alert(1)</script> lumayan');

        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('lumayan');
    }

    public function test_lima_ulasan_atau_kurang_tidak_menampilkan_lihat_selengkapnya(): void
    {
        $this->buatBanyakUlasan(5);

        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Penulis 5')
            ->assertSee('Penulis 1')
            ->assertDontSee('Lihat selengkapnya')
            ->assertDontSee('Tampilkan lebih sedikit');
    }

    public function test_lebih_dari_lima_ulasan_menampilkan_lima_terbaru_dan_tombol(): void
    {
        $this->buatBanyakUlasan(7);

        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Penulis 7')
            ->assertSee('Penulis 3')
            ->assertDontSee('Penulis 2')
            ->assertDontSee('Penulis 1')
            ->assertSee('Lihat selengkapnya')
            ->assertSee('ulasan=semua', false)
            ->assertDontSee('Tampilkan lebih sedikit');
    }

    public function test_lihat_selengkapnya_menampilkan_semua_ulasan(): void
    {
        $this->buatBanyakUlasan(7);

        $this->get(route('lapangan.show', ['field' => $this->lapangan, 'ulasan' => 'semua']))
            ->assertOk()
            ->assertSee('Penulis 7')
            ->assertSee('Penulis 2')
            ->assertSee('Penulis 1')
            ->assertSee('Tampilkan lebih sedikit')
            ->assertDontSee('Lihat selengkapnya');
    }

    public function test_seeder_membuat_ulasan_yang_cocok_dengan_angka_lapangan_dan_menyisakan_booking_demo(): void
    {
        // Lapangan uji dari setUp() senama dengan lapangan seeder; hapus agar tidak tertukar.
        $this->lapangan->delete();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(Review::count(), (int) Field::sum('reviews_count'));

        $vip = Field::where('name', 'Garuda Arena VIP')->firstOrFail();
        $this->assertSame(8, $vip->reviews_count);

        $rataRata = round((float) Review::where('field_id', $vip->id)->avg('rating'), 1);
        $this->assertEqualsWithDelta($rataRata, (float) $vip->rating, 0.01);

        $merdeka = Field::where('name', 'Merdeka Arena')->firstOrFail();
        $this->assertSame(5, $merdeka->reviews_count);

        $demo = Booking::where('code', 'like', '%-L2M2')->firstOrFail();
        $this->assertSame(Booking::STATUS_SELESAI, $demo->status);
        $this->assertTrue($demo->bisaDiulas());
    }
}

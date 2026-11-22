<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RiwayatUlasanTest extends TestCase
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

    private function buatBooking(User $user, string $status = Booking::STATUS_SELESAI, string $tanggal = '2026-11-19'): Booking
    {
        $this->urut++;

        return Booking::create([
            'code' => 'GA-2011-U'.$this->urut,
            'user_id' => $user->id,
            'field_id' => $this->lapangan->id,
            'date' => $tanggal,
            'start_hour' => 19,
            'duration' => 2,
            'price_per_hour' => $this->lapangan->price_per_hour,
            'total' => $this->lapangan->price_per_hour * 2,
            'status' => $status,
        ]);
    }

    public function test_booking_selesai_menampilkan_form_ulasan(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertSee('css/ulasan.css', false)
            ->assertSee('Tulis ulasan')
            ->assertSee(route('riwayat.ulasan', ['booking' => $booking->code]), false);
    }

    public function test_booking_dikonfirmasi_menampilkan_form_ulasan(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-25');

        $this->actingAs($pelanggan)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertSee('Tulis ulasan')
            ->assertSee(route('riwayat.ulasan', ['booking' => $booking->code]), false);
    }

    public function test_booking_yang_belum_dibayar_tidak_menampilkan_form_ulasan(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-25');

        $this->actingAs($pelanggan)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertDontSee('Tulis ulasan')
            ->assertDontSee(route('riwayat.ulasan', ['booking' => $booking->code]), false);
    }

    public function test_booking_yang_sudah_diulas_menampilkan_ulasan_tanpa_form(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan);

        Review::buat($pelanggan, $booking, 4, 'Lantainya bersih dan nyaman.');

        $this->actingAs($pelanggan)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertSee('Ulasan kamu')
            ->assertSee('Lantainya bersih dan nyaman.')
            ->assertSee('★★★★☆')
            ->assertDontSee('Tulis ulasan')
            ->assertDontSee(route('riwayat.ulasan', ['booking' => $booking->code]), false);
    }

    public function test_galat_validasi_hanya_tampil_di_kartu_yang_dikirim(): void
    {
        $pelanggan = User::factory()->create();
        $pertama = $this->buatBooking($pelanggan);
        $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)
            ->from(route('riwayat.index'))
            ->post(route('riwayat.ulasan', ['booking' => $pertama->code]), [
                'kode' => $pertama->code,
                'rating' => 5,
                'comment' => 'ok',
            ])
            ->assertRedirect(route('riwayat.index'));

        $html = $this->actingAs($pelanggan)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Ulasan minimal 5 karakter.'));
    }
}

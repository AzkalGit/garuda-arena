<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReviewTest extends TestCase
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

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatBooking(User $user, string $status = Booking::STATUS_SELESAI, string $tanggal = '2026-11-19'): Booking
    {
        $this->urut++;

        return Booking::create([
            'code' => 'GA-2011-T'.$this->urut,
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

    /** @return array<string, mixed> */
    private function isian(int $rating = 5, string $komentar = 'Lapangan bagus sekali.'): array
    {
        return ['rating' => $rating, 'comment' => $komentar];
    }

    private function kirim(User $user, Booking $booking, array $isian)
    {
        return $this->actingAs($user)->post(route('riwayat.ulasan', ['booking' => $booking->code]), $isian);
    }

    public function test_pelanggan_mengulas_booking_selesai_dan_rating_lapangan_dihitung_ulang(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan);

        $this->kirim($pelanggan, $booking, $this->isian(5))
            ->assertRedirect(route('riwayat.index'))
            ->assertSessionHas('riwayat_pesan');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $pelanggan->id,
            'field_id' => $this->lapangan->id,
            'booking_id' => $booking->id,
            'rating' => 5,
        ]);

        $lapangan = $this->lapangan->fresh();
        $this->assertSame(2, $lapangan->reviews_count);
        $this->assertSame('4.5', (string) $lapangan->rating);
    }

    public function test_booking_dikonfirmasi_yang_jam_mainnya_lewat_menjadi_selesai_dan_bisa_diulas(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-19');

        $this->kirim($pelanggan, $booking, $this->isian())
            ->assertRedirect(route('riwayat.index'));

        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame(Booking::STATUS_SELESAI, $booking->fresh()->status);
    }

    public function test_booking_dikonfirmasi_yang_belum_dimainkan_bisa_diulas(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-25');

        $this->kirim($pelanggan, $booking, $this->isian())
            ->assertRedirect(route('riwayat.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame(Booking::STATUS_DIKONFIRMASI, $booking->fresh()->status);
        $this->assertSame(2, $this->lapangan->fresh()->reviews_count);
    }

    public function test_booking_yang_belum_dibayar_tidak_bisa_diulas(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-25');

        $this->kirim($pelanggan, $booking, $this->isian())
            ->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('reviews', 0);
        $this->assertSame(1, $this->lapangan->fresh()->reviews_count);
    }

    public function test_satu_booking_hanya_bisa_diulas_sekali(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan);

        $this->kirim($pelanggan, $booking, $this->isian(5));
        $this->kirim($pelanggan, $booking, $this->isian(1))
            ->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame(2, $this->lapangan->fresh()->reviews_count);
    }

    public function test_booking_orang_lain_dan_admin_mendapat_404(): void
    {
        $pemilik = User::factory()->create();
        $booking = $this->buatBooking($pemilik);

        $this->kirim(User::factory()->create(), $booking, $this->isian())->assertNotFound();
        $this->kirim($this->buatAdmin(), $booking, $this->isian())->assertNotFound();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $booking = $this->buatBooking(User::factory()->create());

        $this->post(route('riwayat.ulasan', ['booking' => $booking->code]), $this->isian())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_dan_komentar_divalidasi(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan);

        $this->kirim($pelanggan, $booking, $this->isian(6))->assertSessionHasErrors('rating');
        $this->kirim($pelanggan, $booking, $this->isian(0))->assertSessionHasErrors('rating');
        $this->kirim($pelanggan, $booking, $this->isian(5, 'ok'))->assertSessionHasErrors('comment');
        $this->kirim($pelanggan, $booking, ['comment' => 'Lapangan bagus sekali.'])->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_bisa_diulas_hanya_setelah_dikonfirmasi_dan_tanpa_ulasan(): void
    {
        $pelanggan = User::factory()->create();
        $selesai = $this->buatBooking($pelanggan);
        $dikonfirmasi = $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-25');
        $belumBayar = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-25');

        $this->assertTrue($selesai->bisaDiulas());
        $this->assertTrue($dikonfirmasi->bisaDiulas());
        $this->assertFalse($belumBayar->bisaDiulas());

        Review::buat($pelanggan, $selesai, 4, 'Cukup bagus.');

        $this->assertFalse($selesai->fresh()->bisaDiulas());
    }
}

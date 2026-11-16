<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    private Field $lapangan;

    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
        Storage::fake('local');

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
            'rating' => 4.6,
            'reviews_count' => 16,
            'status' => Field::STATUS_AKTIF,
        ]);
    }

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatBooking(User $pemilik, string $status, string $tanggal = '2026-11-21', int $jam = 10, array $lain = []): Booking
    {
        $this->urut++;

        return Booking::create(array_merge([
            'code' => 'GA-TEST-'.$this->urut,
            'user_id' => $pemilik->id,
            'field_id' => $this->lapangan->id,
            'date' => $tanggal,
            'start_hour' => $jam,
            'duration' => 1,
            'price_per_hour' => 180000,
            'total' => 180000,
            'status' => $status,
        ], $lain));
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
    }

    public function test_pelanggan_tidak_boleh_membuka_panel_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.bookings.index'))
            ->assertForbidden();
    }

    public function test_admin_melihat_semua_booking(): void
    {
        $pelanggan = User::factory()->create(['name' => 'Budi Santoso']);
        $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee('GA-TEST-1')
            ->assertSee('Budi Santoso');
    }

    public function test_filter_status_dan_pencarian(): void
    {
        $pelanggan = User::factory()->create(['name' => 'Budi Santoso']);
        $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI);
        $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-22');
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->get(route('admin.bookings.index', ['status' => Booking::STATUS_DIKONFIRMASI]))
            ->assertOk()
            ->assertSee('GA-TEST-2')
            ->assertDontSee('GA-TEST-1');

        $this->actingAs($admin)
            ->get(route('admin.bookings.index', ['q' => 'GA-TEST-1']))
            ->assertOk()
            ->assertSee('GA-TEST-1')
            ->assertDontSee('GA-TEST-2');
    }

    public function test_admin_menyetujui_booking(): void
    {
        $admin = $this->buatAdmin();
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($admin)
            ->post(route('admin.bookings.setujui', ['booking' => $booking->code]))
            ->assertRedirect(route('admin.bookings.index'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_DIKONFIRMASI, $booking->status);
        $this->assertSame($admin->id, $booking->verified_by);
        $this->assertNotNull($booking->verified_at);
    }

    public function test_setujui_gagal_bila_status_bukan_menunggu_verifikasi(): void
    {
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_PEMBAYARAN);

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.bookings.setujui', ['booking' => $booking->code]))
            ->assertSessionHasErrors('booking');

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
    }

    public function test_pelanggan_tidak_boleh_menyetujui(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($pelanggan)
            ->post(route('admin.bookings.setujui', ['booking' => $booking->code]))
            ->assertForbidden();

        $this->assertSame(Booking::STATUS_MENUNGGU_VERIFIKASI, $booking->fresh()->status);
    }

    public function test_tolak_sebelum_jam_main_kembali_ke_menunggu_pembayaran(): void
    {
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-21', 10, [
            'proof_path' => 'bukti-bayar/a.jpg',
            'proof_name' => 'a.jpg',
        ]);
        Storage::disk('local')->put('bukti-bayar/a.jpg', 'x');

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.bookings.tolak', ['booking' => $booking->code]), ['alasan' => 'Nominal tidak sesuai'])
            ->assertRedirect(route('admin.bookings.index'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->status);
        $this->assertSame('Nominal tidak sesuai', $booking->rejection_reason);
        $this->assertNotNull($booking->expires_at);
        $this->assertNull($booking->proof_path);
        Storage::disk('local')->assertMissing('bukti-bayar/a.jpg');
    }

    public function test_tolak_setelah_jam_mulai_lewat_membatalkan_booking(): void
    {
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-20', 8);

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.bookings.tolak', ['booking' => $booking->code]), ['alasan' => 'Bukti tidak terbaca'])
            ->assertRedirect(route('admin.bookings.index'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_DIBATALKAN, $booking->status);
        $this->assertSame('Bukti tidak terbaca', $booking->rejection_reason);
        $this->assertStringContainsString('Bukti tidak terbaca', $booking->cancelled_reason);
    }

    public function test_tolak_tanpa_alasan_ditolak_validasi(): void
    {
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.bookings.tolak', ['booking' => $booking->code]), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');

        $this->assertSame(Booking::STATUS_MENUNGGU_VERIFIKASI, $booking->fresh()->status);
    }

    public function test_pelanggan_tidak_boleh_menolak(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($pelanggan)
            ->post(route('admin.bookings.tolak', ['booking' => $booking->code]), ['alasan' => 'Coba-coba saja'])
            ->assertForbidden();
    }

    public function test_admin_dapat_membuka_bukti(): void
    {
        Storage::disk('local')->put('bukti-bayar/b.jpg', 'isi-gambar');
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-21', 10, [
            'proof_path' => 'bukti-bayar/b.jpg',
            'proof_name' => 'b.jpg',
        ]);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.bookings.bukti', ['booking' => $booking->code]))
            ->assertOk();
    }

    public function test_bukti_yang_berkasnya_tidak_ada_kembali_dengan_pesan(): void
    {
        $booking = $this->buatBooking(User::factory()->create(), Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-21', 10, [
            'proof_name' => 'demo.jpg',
        ]);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.bookings.bukti', ['booking' => $booking->code]))
            ->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHasErrors('booking');
    }

    public function test_pelanggan_tidak_boleh_membuka_bukti(): void
    {
        $pelanggan = User::factory()->create();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($pelanggan)
            ->get(route('admin.bookings.bukti', ['booking' => $booking->code]))
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RiwayatTest extends TestCase
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
            'rating' => 4.6,
            'reviews_count' => 16,
            'status' => Field::STATUS_AKTIF,
        ]);
    }

    private function buatPelanggan(): User
    {
        return User::factory()->create();
    }

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatBooking(
        User $user,
        string $status = Booking::STATUS_MENUNGGU_PEMBAYARAN,
        string $tanggal = '2026-11-21',
        int $jam = 19,
        int $durasi = 2,
        array $tambahan = [],
    ): Booking {
        return Booking::create(array_merge([
            'code' => 'GA-9999-'.sprintf('%04d', ++$this->urut),
            'user_id' => $user->id,
            'field_id' => $this->lapangan->id,
            'date' => $tanggal,
            'start_hour' => $jam,
            'duration' => $durasi,
            'price_per_hour' => $this->lapangan->price_per_hour,
            'total' => $this->lapangan->price_per_hour * $durasi,
            'status' => $status,
        ], $tambahan));
    }

    // ---------- Halaman riwayat ----------

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('riwayat.index'))->assertRedirect(route('login'));
    }

    public function test_admin_tidak_boleh_membuka_riwayat(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('riwayat.index'))
            ->assertForbidden();
    }

    public function test_riwayat_hanya_menampilkan_booking_milik_sendiri(): void
    {
        $saya = $this->buatPelanggan();
        $lain = $this->buatPelanggan();
        $milikSaya = $this->buatBooking($saya);
        $milikLain = $this->buatBooking($lain);

        $this->actingAs($saya)
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertSee($milikSaya->code)
            ->assertDontSee($milikLain->code);
    }

    public function test_booking_terbaru_tampil_paling_atas(): void
    {
        $saya = $this->buatPelanggan();
        $lama = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-21', 8, 1);
        $baru = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-22', 8, 1);

        $this->actingAs($saya)
            ->get(route('riwayat.index'))
            ->assertSeeInOrder([$baru->code, $lama->code]);
    }

    public function test_keadaan_kosong_tampil_bila_belum_ada_booking(): void
    {
        $this->actingAs($this->buatPelanggan())
            ->get(route('riwayat.index'))
            ->assertOk()
            ->assertSee('Belum ada booking');
    }

    public function test_filter_status_hanya_menampilkan_status_itu(): void
    {
        $saya = $this->buatPelanggan();
        $menunggu = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-21', 8, 1);
        $konfirmasi = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-22', 8, 1);

        $this->actingAs($saya)
            ->get(route('riwayat.index', ['status' => Booking::STATUS_DIKONFIRMASI]))
            ->assertSee($konfirmasi->code)
            ->assertDontSee($menunggu->code);
    }

    public function test_status_tak_dikenal_pada_filter_diabaikan(): void
    {
        $saya = $this->buatPelanggan();
        $booking = $this->buatBooking($saya);

        $this->actingAs($saya)
            ->get(route('riwayat.index', ['status' => 'ngawur']))
            ->assertOk()
            ->assertSee($booking->code);
    }

    public function test_booking_yang_lewat_batas_bayar_tampil_dibatalkan(): void
    {
        $saya = $this->buatPelanggan();
        $booking = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-21', 19, 2, [
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($saya)->get(route('riwayat.index'))->assertOk();

        $this->assertSame(Booking::STATUS_DIBATALKAN, $booking->fresh()->status);
    }

    // ---------- Batalkan ----------

    public function test_pelanggan_membatalkan_booking_sendiri(): void
    {
        $saya = $this->buatPelanggan();
        $booking = $this->buatBooking($saya);

        $this->actingAs($saya)
            ->post(route('riwayat.batal', ['booking' => $booking->code]))
            ->assertRedirect(route('riwayat.index'))
            ->assertSessionHas('riwayat_pesan');

        $booking->refresh();
        $this->assertSame(Booking::STATUS_DIBATALKAN, $booking->status);
        $this->assertSame('Dibatalkan pelanggan', $booking->cancelled_reason);
    }

    public function test_booking_orang_lain_tidak_bisa_dibatalkan(): void
    {
        $lain = $this->buatPelanggan();
        $booking = $this->buatBooking($lain);

        $this->actingAs($this->buatPelanggan())
            ->post(route('riwayat.batal', ['booking' => $booking->code]))
            ->assertNotFound();

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
    }

    public function test_admin_tidak_bisa_membatalkan_booking_pelanggan(): void
    {
        $booking = $this->buatBooking($this->buatPelanggan());

        $this->actingAs($this->buatAdmin())
            ->post(route('riwayat.batal', ['booking' => $booking->code]))
            ->assertNotFound();

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
    }

    public function test_tamu_tidak_bisa_membatalkan(): void
    {
        $booking = $this->buatBooking($this->buatPelanggan());

        $this->post(route('riwayat.batal', ['booking' => $booking->code]))
            ->assertRedirect(route('login'));

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
    }

    public function test_hanya_menunggu_pembayaran_yang_bisa_dibatalkan(): void
    {
        $saya = $this->buatPelanggan();

        foreach ([
            Booking::STATUS_MENUNGGU_VERIFIKASI,
            Booking::STATUS_DIKONFIRMASI,
            Booking::STATUS_SELESAI,
            Booking::STATUS_DIBATALKAN,
        ] as $status) {
            $booking = $this->buatBooking($saya, $status, '2026-11-25', 8, 1);

            $this->actingAs($saya)
                ->post(route('riwayat.batal', ['booking' => $booking->code]))
                ->assertSessionHasErrors('booking');

            $this->assertSame($status, $booking->fresh()->status);
            $this->assertNotSame('Dibatalkan pelanggan', $booking->fresh()->cancelled_reason);
        }
    }

    public function test_slot_terbebas_setelah_booking_dibatalkan(): void
    {
        $saya = $this->buatPelanggan();
        $booking = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_PEMBAYARAN, '2026-11-21', 19, 2);
        $tanggal = Carbon::parse('2026-11-21');

        $sebelum = collect(Booking::petaJam($this->lapangan, $tanggal))->firstWhere('jam', 19);
        $this->assertFalse($sebelum['tersedia']);

        $this->actingAs($saya)->post(route('riwayat.batal', ['booking' => $booking->code]));

        $sesudah = collect(Booking::petaJam($this->lapangan, $tanggal))->firstWhere('jam', 19);
        $this->assertTrue($sesudah['tersedia']);
    }

    // ---------- Transisi selesai ----------

    public function test_dikonfirmasi_yang_jam_mainnya_lewat_menjadi_selesai(): void
    {
        $saya = $this->buatPelanggan();
        $lewat = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-20', 8, 1);
        $tepat = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-20', 9, 1);
        $berjalan = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-20', 9, 2);
        $besok = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-21', 8, 1);
        $kemarin = $this->buatBooking($saya, Booking::STATUS_DIKONFIRMASI, '2026-11-19', 20, 2);

        Booking::perbaruiStatus();

        $this->assertSame(Booking::STATUS_SELESAI, $lewat->fresh()->status);
        $this->assertSame(Booking::STATUS_SELESAI, $tepat->fresh()->status);
        $this->assertSame(Booking::STATUS_SELESAI, $kemarin->fresh()->status);
        $this->assertSame(Booking::STATUS_DIKONFIRMASI, $berjalan->fresh()->status);
        $this->assertSame(Booking::STATUS_DIKONFIRMASI, $besok->fresh()->status);
    }

    public function test_status_selain_dikonfirmasi_tidak_menjadi_selesai(): void
    {
        $saya = $this->buatPelanggan();
        $verifikasi = $this->buatBooking($saya, Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-19', 8, 1);
        $dibatalkan = $this->buatBooking($saya, Booking::STATUS_DIBATALKAN, '2026-11-19', 10, 1);

        Booking::perbaruiStatus();

        $this->assertSame(Booking::STATUS_MENUNGGU_VERIFIKASI, $verifikasi->fresh()->status);
        $this->assertSame(Booking::STATUS_DIBATALKAN, $dibatalkan->fresh()->status);
    }
}

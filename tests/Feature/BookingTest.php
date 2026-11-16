<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Field $lapangan;

    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
        $this->lapangan = $this->buatLapangan();
    }

    private function buatLapangan(string $status = Field::STATUS_AKTIF, string $nama = 'Garuda Arena VIP'): Field
    {
        return Field::create([
            'name' => $nama,
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
            'status' => $status,
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
        Field $field,
        string $tanggal,
        int $jam,
        int $durasi,
        string $status = Booking::STATUS_MENUNGGU_PEMBAYARAN,
        array $tambahan = [],
    ): Booking {
        return Booking::create(array_merge([
            'code' => 'GA-9999-'.sprintf('%04d', ++$this->urut),
            'user_id' => $user->id,
            'field_id' => $field->id,
            'date' => $tanggal,
            'start_hour' => $jam,
            'duration' => $durasi,
            'price_per_hour' => $field->price_per_hour,
            'total' => $field->price_per_hour * $durasi,
            'status' => $status,
        ], $tambahan));
    }

    private function kirim(User $user, array $data = [])
    {
        return $this->actingAs($user)->post(route('booking.store'), array_merge([
            'field_id' => $this->lapangan->id,
            'tanggal' => '2026-11-21',
            'start_hour' => 19,
            'duration' => 2,
        ], $data));
    }

    // ---------- Pembuatan booking ----------

    public function test_pelanggan_berhasil_membuat_booking(): void
    {
        $user = $this->buatPelanggan();

        $this->kirim($user)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('lapangan.show', ['field' => $this->lapangan->id, 'tanggal' => '2026-11-21']));

        $booking = Booking::firstOrFail();
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame($this->lapangan->id, $booking->field_id);
        $this->assertSame('2026-11-21', $booking->date->toDateString());
        $this->assertSame(19, $booking->start_hour);
        $this->assertSame(2, $booking->duration);
        $this->assertSame(360000, $booking->total);
        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->status);
        $this->assertMatchesRegularExpression('/^GA-2111-[A-Z0-9]{4}$/', $booking->code);
        $this->assertTrue($booking->expires_at->equalTo(now()->addMinutes(60)));
    }

    public function test_harga_per_jam_disalin_ke_booking(): void
    {
        $this->kirim($this->buatPelanggan())->assertSessionHasNoErrors();

        $this->lapangan->update(['price_per_hour' => 999000]);

        $booking = Booking::firstOrFail();
        $this->assertSame(180000, $booking->price_per_hour);
        $this->assertSame(360000, $booking->total);
    }

    public function test_pesan_sukses_tampil_setelah_booking(): void
    {
        $this->actingAs($this->buatPelanggan())
            ->followingRedirects()
            ->post(route('booking.store'), [
                'field_id' => $this->lapangan->id,
                'tanggal' => '2026-11-21',
                'start_hour' => 19,
                'duration' => 2,
            ])
            ->assertOk()
            ->assertSee('Booking berhasil dibuat')
            ->assertSee('GA-2111-');
    }

    // ---------- Cek bentrok ----------

    public function test_booking_bentrok_sama_persis_dan_sebagian_ditolak(): void
    {
        $andi = $this->buatPelanggan();
        $this->buatBooking($andi, $this->lapangan, '2026-11-21', 19, 2);
        $sari = $this->buatPelanggan();

        // sama persis, tumpang tindih di awal, di akhir, dan di dalam
        foreach ([[19, 2], [18, 2], [20, 2], [20, 1]] as [$jam, $durasi]) {
            $this->kirim($sari, ['start_hour' => $jam, 'duration' => $durasi])
                ->assertSessionHasErrors('start_hour');
        }

        $this->assertSame(1, Booking::count());
    }

    public function test_booking_bertetangga_boleh(): void
    {
        $andi = $this->buatPelanggan();
        $this->buatBooking($andi, $this->lapangan, '2026-11-21', 19, 2); // 19-21
        $sari = $this->buatPelanggan();

        $this->kirim($sari, ['start_hour' => 21, 'duration' => 1])->assertSessionHasNoErrors(); // 21-22
        $this->kirim($sari, ['start_hour' => 17, 'duration' => 2])->assertSessionHasNoErrors(); // 17-19

        $this->assertSame(3, Booking::count());
    }

    public function test_booking_dibatalkan_tidak_menghalangi(): void
    {
        $andi = $this->buatPelanggan();
        $this->buatBooking($andi, $this->lapangan, '2026-11-21', 19, 2, Booking::STATUS_DIBATALKAN);

        $this->kirim($this->buatPelanggan())->assertSessionHasNoErrors();

        $this->assertSame(2, Booking::count());
    }

    public function test_lapangan_atau_tanggal_lain_tidak_menghalangi(): void
    {
        $andi = $this->buatPelanggan();
        $lain = $this->buatLapangan(Field::STATUS_AKTIF, 'Merdeka Arena');
        $this->buatBooking($andi, $lain, '2026-11-21', 19, 2);
        $this->buatBooking($andi, $this->lapangan, '2026-11-22', 19, 2);

        $this->kirim($this->buatPelanggan())->assertSessionHasNoErrors();

        $this->assertSame(3, Booking::count());
    }

    public function test_jam_yang_sudah_lewat_hari_ini_ditolak(): void
    {
        $this->kirim($this->buatPelanggan(), ['tanggal' => '2026-11-20', 'start_hour' => 9, 'duration' => 1])
            ->assertSessionHasErrors('start_hour');

        $this->assertSame(0, Booking::count());
    }

    public function test_jam_yang_tepat_sekarang_masih_boleh(): void
    {
        $this->kirim($this->buatPelanggan(), ['tanggal' => '2026-11-20', 'start_hour' => 10, 'duration' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Booking::count());
    }

    public function test_jam_berjalan_lewat_menit_nol_ditolak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:30:00'));

        $this->kirim($this->buatPelanggan(), ['tanggal' => '2026-11-20', 'start_hour' => 10, 'duration' => 1])
            ->assertSessionHasErrors('start_hour');

        $this->assertSame(0, Booking::count());
    }

    public function test_durasi_melewati_jam_tutup_ditolak(): void
    {
        $user = $this->buatPelanggan();

        $this->kirim($user, ['start_hour' => 22, 'duration' => 2])->assertSessionHasErrors('start_hour');
        $this->assertSame(0, Booking::count());

        $this->kirim($user, ['start_hour' => 22, 'duration' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, Booking::count());
    }

    public function test_lapangan_nonaktif_tidak_bisa_dipesan(): void
    {
        $nonaktif = $this->buatLapangan(Field::STATUS_NONAKTIF, 'Lampuuk Turf');

        $this->kirim($this->buatPelanggan(), ['field_id' => $nonaktif->id])
            ->assertSessionHasErrors('field_id');

        $this->assertSame(0, Booking::count());
    }

    // ---------- Otorisasi dan validasi ----------

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post(route('booking.store'), [
            'field_id' => $this->lapangan->id,
            'tanggal' => '2026-11-21',
            'start_hour' => 19,
            'duration' => 2,
        ])->assertRedirect(route('login'));

        $this->assertSame(0, Booking::count());
    }

    public function test_admin_tidak_bisa_membuat_booking(): void
    {
        $this->kirim($this->buatAdmin())->assertForbidden();

        $this->assertSame(0, Booking::count());
    }

    public function test_input_tidak_valid_ditolak(): void
    {
        $user = $this->buatPelanggan();

        $this->kirim($user, ['duration' => 4])->assertSessionHasErrors('duration');
        $this->kirim($user, ['start_hour' => 7])->assertSessionHasErrors('start_hour');
        $this->kirim($user, ['tanggal' => '2026-11-19'])->assertSessionHasErrors('tanggal');
        $this->kirim($user, ['tanggal' => 'ngawur'])->assertSessionHasErrors('tanggal');

        $this->assertSame(0, Booking::count());
    }

    // ---------- Kedaluwarsa ----------

    public function test_booking_lewat_batas_bayar_menjadi_dibatalkan(): void
    {
        $user = $this->buatPelanggan();
        $habis = $this->buatBooking($user, $this->lapangan, '2026-11-21', 10, 1, Booking::STATUS_MENUNGGU_PEMBAYARAN, [
            'expires_at' => now()->subMinute(),
        ]);
        $masihAda = $this->buatBooking($user, $this->lapangan, '2026-11-21', 12, 1, Booking::STATUS_MENUNGGU_PEMBAYARAN, [
            'expires_at' => now()->addMinutes(5),
        ]);

        Booking::perbaruiStatus();

        $this->assertSame(Booking::STATUS_DIBATALKAN, $habis->fresh()->status);
        $this->assertSame('Waktu pembayaran habis', $habis->fresh()->cancelled_reason);
        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $masihAda->fresh()->status);
    }

    public function test_booking_kedaluwarsa_membebaskan_slot(): void
    {
        $andi = $this->buatPelanggan();
        $this->buatBooking($andi, $this->lapangan, '2026-11-21', 19, 2, Booking::STATUS_MENUNGGU_PEMBAYARAN, [
            'expires_at' => now()->subMinute(),
        ]);

        $this->kirim($this->buatPelanggan())->assertSessionHasNoErrors();

        $this->assertSame(1, Booking::where('status', '!=', Booking::STATUS_DIBATALKAN)->count());
    }

    // ---------- Halaman detail ----------

    public function test_detail_menandai_slot_terisi_dan_lewat(): void
    {
        $this->buatBooking($this->buatPelanggan(), $this->lapangan, '2026-11-21', 19, 2);

        $this->get(route('lapangan.show', ['field' => $this->lapangan, 'tanggal' => '2026-11-21']))
            ->assertOk()
            ->assertSee('Terisi');

        $this->get(route('lapangan.show', ['field' => $this->lapangan, 'tanggal' => '2026-11-20']))
            ->assertOk()
            ->assertSee('Lewat');
    }

    public function test_tanggal_tidak_valid_atau_lewat_diganti_hari_ini(): void
    {
        $this->get(route('lapangan.show', ['field' => $this->lapangan, 'tanggal' => 'ngawur']))
            ->assertOk()
            ->assertSee('value="2026-11-20"', false);

        $this->get(route('lapangan.show', ['field' => $this->lapangan, 'tanggal' => '2020-01-01']))
            ->assertOk()
            ->assertSee('value="2026-11-20"', false);
    }

    public function test_tamu_melihat_tautan_masuk_bukan_tombol_booking(): void
    {
        $this->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Masuk untuk Booking')
            ->assertDontSee('Booking Sekarang');
    }

    public function test_admin_tidak_melihat_tombol_booking(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Akun admin tidak dapat membuat booking.')
            ->assertDontSee('Booking Sekarang');
    }

    public function test_pelanggan_melihat_tombol_booking(): void
    {
        $this->actingAs($this->buatPelanggan())
            ->get(route('lapangan.show', $this->lapangan))
            ->assertOk()
            ->assertSee('Booking Sekarang');
    }
}

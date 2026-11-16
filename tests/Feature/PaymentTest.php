<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    private function buatBooking(User $user, string $status = Booking::STATUS_MENUNGGU_PEMBAYARAN, array $tambahan = []): Booking
    {
        return Booking::create(array_merge([
            'code' => 'GA-2111-'.sprintf('%04d', ++$this->urut),
            'user_id' => $user->id,
            'field_id' => $this->lapangan->id,
            'date' => '2026-11-21',
            'start_hour' => 19,
            'duration' => 2,
            'price_per_hour' => 180000,
            'total' => 360000,
            'status' => $status,
            'expires_at' => $status === Booking::STATUS_MENUNGGU_PEMBAYARAN ? now()->addMinutes(30) : null,
        ], $tambahan));
    }

    private function buktiValid(): UploadedFile
    {
        return UploadedFile::fake()->create('bukti.png', 100, 'image/png');
    }

    private function urlShow(Booking $booking): string
    {
        return route('pembayaran.show', ['booking' => $booking->code]);
    }

    private function urlStore(Booking $booking): string
    {
        return route('pembayaran.store', ['booking' => $booking->code]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $booking = $this->buatBooking($this->buatPelanggan());

        $this->get($this->urlShow($booking))->assertRedirect(route('login'));
        $this->post($this->urlStore($booking), ['proof' => $this->buktiValid()])->assertRedirect(route('login'));
    }

    public function test_pemilik_melihat_halaman_pembayaran(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)->get($this->urlShow($booking))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee('Rp 360.000')
            ->assertSee('1234 5678 9012');
    }

    public function test_orang_lain_mendapat_404(): void
    {
        $booking = $this->buatBooking($this->buatPelanggan());
        $orangLain = $this->buatPelanggan();

        $this->actingAs($orangLain)->get($this->urlShow($booking))->assertNotFound();
        $this->actingAs($orangLain)->post($this->urlStore($booking), ['proof' => $this->buktiValid()])->assertNotFound();

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
        $this->assertNull($booking->fresh()->proof_path);
    }

    public function test_admin_bukan_pemilik_mendapat_404(): void
    {
        $booking = $this->buatBooking($this->buatPelanggan());
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->get($this->urlShow($booking))->assertNotFound();
        $this->actingAs($admin)->post($this->urlStore($booking), ['proof' => $this->buktiValid()])->assertNotFound();
    }

    public function test_kirim_bukti_mengubah_status_dan_mencatat_data(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)->post($this->urlStore($booking), ['proof' => $this->buktiValid()])
            ->assertRedirect($this->urlShow($booking))
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $this->assertSame(Booking::STATUS_MENUNGGU_VERIFIKASI, $booking->status);
        $this->assertNull($booking->expires_at);
        $this->assertSame('bukti.png', $booking->proof_name);
        $this->assertNotNull($booking->proof_submitted_at);
        Storage::disk('local')->assertExists($booking->proof_path);
    }

    public function test_bukti_tersimpan_di_disk_privat_bukan_public(): void
    {
        Storage::fake('public');
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)->post($this->urlStore($booking), ['proof' => $this->buktiValid()]);

        $path = $booking->fresh()->proof_path;

        $this->assertStringStartsWith('bukti-bayar/', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_tipe_berkas_salah_ditolak(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        foreach (['bukti.pdf' => 'application/pdf', 'bukti.gif' => 'image/gif', 'bukti.txt' => 'text/plain'] as $nama => $mime) {
            $this->actingAs($pelanggan)
                ->post($this->urlStore($booking), ['proof' => UploadedFile::fake()->create($nama, 100, $mime)])
                ->assertSessionHasErrors('proof');
        }

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
        $this->assertNull($booking->fresh()->proof_path);
    }

    public function test_ukuran_lebih_dari_2_mb_ditolak(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)
            ->post($this->urlStore($booking), ['proof' => UploadedFile::fake()->create('besar.jpg', 2049, 'image/jpeg')])
            ->assertSessionHasErrors('proof');

        $this->assertNull($booking->fresh()->proof_path);
    }

    public function test_tanpa_berkas_ditolak(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan);

        $this->actingAs($pelanggan)->post($this->urlStore($booking), [])->assertSessionHasErrors('proof');

        $this->assertSame(Booking::STATUS_MENUNGGU_PEMBAYARAN, $booking->fresh()->status);
    }

    public function test_booking_kedaluwarsa_ditolak_dan_dibatalkan(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_PEMBAYARAN, [
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($pelanggan)->post($this->urlStore($booking), ['proof' => $this->buktiValid()])
            ->assertSessionHasErrors('proof');

        $booking->refresh();

        $this->assertSame(Booking::STATUS_DIBATALKAN, $booking->status);
        $this->assertNull($booking->proof_path);
    }

    public function test_status_selain_menunggu_pembayaran_ditolak(): void
    {
        $pelanggan = $this->buatPelanggan();

        foreach ([Booking::STATUS_MENUNGGU_VERIFIKASI, Booking::STATUS_DIKONFIRMASI, Booking::STATUS_DIBATALKAN] as $status) {
            $booking = $this->buatBooking($pelanggan, $status);

            $this->actingAs($pelanggan)->post($this->urlStore($booking), ['proof' => $this->buktiValid()])
                ->assertSessionHasErrors('proof');

            $this->assertNull($booking->fresh()->proof_path);
            $this->assertSame($status, $booking->fresh()->status);
        }
    }

    public function test_halaman_booking_kedaluwarsa_menampilkan_pembatalan(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_PEMBAYARAN, [
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($pelanggan)->get($this->urlShow($booking))
            ->assertOk()
            ->assertSee('Waktu pembayaran habis');

        $this->assertSame(Booking::STATUS_DIBATALKAN, $booking->fresh()->status);
    }

    public function test_halaman_menunggu_verifikasi_tanpa_form(): void
    {
        $pelanggan = $this->buatPelanggan();
        $booking = $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI, [
            'proof_name' => 'bukti.png',
        ]);

        $this->actingAs($pelanggan)->get($this->urlShow($booking))
            ->assertOk()
            ->assertSee('menunggu verifikasi admin')
            ->assertDontSee('Kirim Bukti Pembayaran');
    }

    public function test_booking_baru_diarahkan_ke_halaman_pembayaran(): void
    {
        $pelanggan = $this->buatPelanggan();

        $this->actingAs($pelanggan)->post(route('booking.store'), [
            'field_id' => $this->lapangan->id,
            'tanggal' => '2026-11-21',
            'start_hour' => 19,
            'duration' => 2,
        ]);

        $booking = Booking::query()->firstOrFail();

        $this->assertSame($pelanggan->id, $booking->user_id);
        $this->actingAs($pelanggan)
            ->post(route('booking.store'), [
                'field_id' => $this->lapangan->id,
                'tanggal' => '2026-11-22',
                'start_hour' => 10,
                'duration' => 1,
            ])
            ->assertRedirect($this->urlShow(Booking::query()->latest('id')->firstOrFail()));
    }
}
<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
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

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatBooking(User $pemilik, string $status, string $tanggal, int $jam, int $total): Booking
    {
        $this->urut++;

        return Booking::create([
            'code' => 'GA-DASH-'.$this->urut,
            'user_id' => $pemilik->id,
            'field_id' => $this->lapangan->id,
            'date' => $tanggal,
            'start_hour' => $jam,
            'duration' => 1,
            'price_per_hour' => $total,
            'total' => $total,
            'status' => $status,
        ]);
    }

    private function isiData(): void
    {
        $pelanggan = User::factory()->create();

        $this->buatBooking($pelanggan, Booking::STATUS_DIKONFIRMASI, '2026-11-20', 18, 180000);
        $this->buatBooking($pelanggan, Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-20', 20, 360000);
        $this->buatBooking($pelanggan, Booking::STATUS_DIBATALKAN, '2026-11-20', 12, 500000);
        $this->buatBooking($pelanggan, Booking::STATUS_SELESAI, '2026-11-18', 9, 100000);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_pelanggan_mendapat_403(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_melihat_kartu_dengan_angka_benar(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Rp 280.000')
            ->assertSee('1 dari 1')
            ->assertViewHas('bookingHariIni', 2)
            ->assertViewHas('menungguVerifikasi', 1)
            ->assertViewHas('pendapatanLabel', 'Rp 280.000')
            ->assertViewHas('lapanganAktifLabel', '1 dari 1');
    }

    public function test_grafik_menghitung_tujuh_hari_terakhir(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin')
            ->assertViewHas('grafik', function (array $grafik) {
                return count($grafik) === 7
                    && $grafik[0]['nilai'] === 0
                    && $grafik[4]['nilai'] === 1
                    && $grafik[6]['nilai'] === 2;
            });
    }

    public function test_booking_terbaru_dibatasi_lima(): void
    {
        $pelanggan = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->buatBooking($pelanggan, Booking::STATUS_SELESAI, '2026-11-18', 8 + $i, 100000);
        }

        $this->actingAs($this->buatAdmin())
            ->get('/admin')
            ->assertViewHas('terbaru', fn ($terbaru) => $terbaru->count() === 5);
    }

    public function test_sidebar_memuat_tautan_dashboard(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin')
            ->assertSee(route('admin.dashboard'));
    }
}

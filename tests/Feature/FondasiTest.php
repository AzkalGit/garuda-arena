<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FondasiTest extends TestCase
{
    use RefreshDatabase;

    private function buatLapangan(): Field
    {
        return Field::create([
            'name' => 'Lapangan Uji',
            'type' => Field::TYPE_INTERLOCK,
            'price_per_hour' => 100000,
            'location' => 'Gedung Uji',
            'description' => 'Deskripsi uji.',
            'size' => '25 x 15 m',
            'capacity' => '10 pemain',
            'facilities' => ['Parkir Luas', 'Musala'],
            'image' => 'images/uji.jpg',
            'rating' => 4.5,
            'reviews_count' => 3,
            'status' => Field::STATUS_AKTIF,
        ]);
    }

    private function buatBooking(User $user, Field $field): Booking
    {
        return Booking::create([
            'code' => 'GA-0101-TEST',
            'user_id' => $user->id,
            'field_id' => $field->id,
            'date' => '2026-12-01',
            'start_hour' => 19,
            'duration' => 2,
            'price_per_hour' => $field->price_per_hour,
            'total' => $field->price_per_hour * 2,
            'status' => Booking::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
    }

    public function test_peran_bawaan_adalah_pelanggan(): void
    {
        $user = User::factory()->create()->refresh();

        $this->assertSame(User::ROLE_PELANGGAN, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_true_untuk_peran_admin(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->assertTrue($user->isAdmin());
    }

    public function test_registrasi_publik_selalu_pelanggan(): void
    {
        $this->post('/register', [
            'name' => 'Calon Admin',
            'email' => 'calon@example.com',
            'password' => 'password-uji-123',
            'password_confirmation' => 'password-uji-123',
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::where('email', 'calon@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_PELANGGAN, $user->role);
    }

    public function test_konstanta_status_dan_peran(): void
    {
        $this->assertSame('admin', User::ROLE_ADMIN);
        $this->assertSame('pelanggan', User::ROLE_PELANGGAN);
        $this->assertSame('aktif', Field::STATUS_AKTIF);
        $this->assertSame('nonaktif', Field::STATUS_NONAKTIF);
        $this->assertSame('menunggu_pembayaran', Booking::STATUS_MENUNGGU_PEMBAYARAN);
        $this->assertSame('menunggu_verifikasi', Booking::STATUS_MENUNGGU_VERIFIKASI);
        $this->assertSame('dikonfirmasi', Booking::STATUS_DIKONFIRMASI);
        $this->assertSame('dibatalkan', Booking::STATUS_DIBATALKAN);
        $this->assertSame('selesai', Booking::STATUS_SELESAI);
    }

    public function test_relasi_user_field_booking(): void
    {
        $user = User::factory()->create();
        $field = $this->buatLapangan();
        $booking = $this->buatBooking($user, $field);

        $this->assertTrue($booking->user->is($user));
        $this->assertTrue($booking->field->is($field));
        $this->assertTrue($user->bookings->first()->is($booking));
        $this->assertTrue($field->bookings->first()->is($booking));
    }

    public function test_facilities_dibaca_sebagai_array_dan_jam_selesai(): void
    {
        $user = User::factory()->create();
        $field = $this->buatLapangan()->fresh();
        $booking = $this->buatBooking($user, $field);

        $this->assertSame(['Parkir Luas', 'Musala'], $field->facilities);
        $this->assertTrue($field->isActive());
        $this->assertSame(21, $booking->endHour());
        $this->assertSame(200000, $booking->total);
    }

    public function test_seeder_membuat_data_awal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(8, Field::count());
        $this->assertSame(7, Field::where('status', Field::STATUS_AKTIF)->count());
        $this->assertSame(1, Field::where('status', Field::STATUS_NONAKTIF)->count());
        $this->assertSame(1, User::where('role', User::ROLE_ADMIN)->count());
        $this->assertSame(3, User::where('role', User::ROLE_PELANGGAN)->count());
        $this->assertGreaterThan(0, Booking::count());
    }

    public function test_booking_demo_dari_seeder_tidak_bentrok(): void
    {
        $this->seed(DatabaseSeeder::class);

        $aktif = Booking::where('status', '!=', Booking::STATUS_DIBATALKAN)->get();
        $this->assertGreaterThan(1, $aktif->count());

        $bentrok = [];

        foreach ($aktif as $a) {
            foreach ($aktif as $b) {
                if ($a->id >= $b->id) {
                    continue;
                }
                if ($a->field_id != $b->field_id || ! $a->date->equalTo($b->date)) {
                    continue;
                }
                if ($a->start_hour < $b->endHour() && $b->start_hour < $a->endHour()) {
                    $bentrok[] = "{$a->code} dan {$b->code}";
                }
            }
        }

        $this->assertSame([], $bentrok);
    }
}

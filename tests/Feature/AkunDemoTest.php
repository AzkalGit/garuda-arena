<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AkunDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_demo_memakai_email_dan_sandi_baru(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'garuda@gmail.com')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue(Hash::check('garuda123', $admin->password));
        $this->assertFalse(User::where('email', 'admin@g')->exists());
    }

    public function test_empat_pelanggan_demo_punya_sandi_riwayat_dan_ulasan_sendiri(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['azkal', 'furqan', 'fandi', 'fadel'] as $nama) {
            $user = User::where('email', $nama.'@gmail.com')->firstOrFail();

            $this->assertSame(User::ROLE_PELANGGAN, $user->role);
            $this->assertTrue(Hash::check($nama.'123', $user->password));
            $this->assertGreaterThanOrEqual(4, Booking::where('user_id', $user->id)->count());
            $this->assertSame(2, Review::where('user_id', $user->id)->count());
        }
    }

    public function test_login_pelanggan_demo_berhasil(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', ['email' => 'azkal@gmail.com', 'password' => 'azkal123']);

        $this->assertAuthenticated();
    }

    public function test_booking_seeder_tidak_saling_bentrok(): void
    {
        $this->seed(DatabaseSeeder::class);

        $aktif = Booking::where('status', '!=', Booking::STATUS_DIBATALKAN)->get();

        $this->assertGreaterThan(40, $aktif->count());

        foreach ($aktif as $a) {
            foreach ($aktif as $b) {
                if ($a->id >= $b->id || $a->field_id !== $b->field_id) {
                    continue;
                }
                if (Carbon::parse($a->date)->toDateString() !== Carbon::parse($b->date)->toDateString()) {
                    continue;
                }

                $this->assertFalse(
                    $a->start_hour < $b->endHour() && $b->start_hour < $a->endHour(),
                    "Bentrok: {$a->code} dan {$b->code}"
                );
            }
        }
    }
}

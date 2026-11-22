<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Field;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Kata sandi demo SEMENTARA. Ganti sendiri; jangan dipakai di server publik.
    private const DEMO_PASSWORD = 'admin123';

    public function run(): void
    {
        $admin = $this->buatUser('Admin Garuda', 'admin@g', '081200000001', User::ROLE_ADMIN);
        $andi = $this->buatUser('Andi Pratama', 'andi@garudaarena.test', '081200000002', User::ROLE_PELANGGAN);
        $sari = $this->buatUser('Sari Wulandari', 'sari@garudaarena.test', '081200000003', User::ROLE_PELANGGAN);
        $budi = $this->buatUser('Budi Santoso', 'budi@garudaarena.test', '081200000004', User::ROLE_PELANGGAN);

        $lapangan = [];
        foreach ($this->dataLapangan() as $data) {
            $lapangan[] = Field::create($data);
        }

        $hariIni = Carbon::today();
        $besok = $hariIni->copy()->addDay();
        $lusa = $hariIni->copy()->addDays(2);
        $hari3 = $hariIni->copy()->addDays(3);
        $kemarin = $hariIni->copy()->subDay();

        $konfirmasi = ['verified_by' => $admin->id, 'verified_at' => now()];

        // Dikonfirmasi
        $this->buatBooking($andi, $lapangan[0], $besok, 19, 2, Booking::STATUS_DIKONFIRMASI, 'A1B1', $konfirmasi);
        $this->buatBooking($budi, $lapangan[6], $hari3, 20, 2, Booking::STATUS_DIKONFIRMASI, 'C3D3', $konfirmasi);

        // Menunggu verifikasi (proof_path sengaja kosong: berkas demo tidak ada)
        $this->buatBooking($sari, $lapangan[1], $besok, 20, 1, Booking::STATUS_MENUNGGU_VERIFIKASI, 'E5F5', [
            'proof_name' => 'bukti-demo.jpg',
            'proof_submitted_at' => now(),
        ]);

        // Menunggu pembayaran
        $this->buatBooking($andi, $lapangan[2], $lusa, 18, 2, Booking::STATUS_MENUNGGU_PEMBAYARAN, 'G7H7', [
            'expires_at' => now()->addMinutes((int) config('garuda.pay_window_minutes')),
        ]);

        // Dibatalkan
        $this->buatBooking($budi, $lapangan[3], $besok, 16, 1, Booking::STATUS_DIBATALKAN, 'J9K9', [
            'cancelled_reason' => 'Dibatalkan pelanggan',
        ]);

        // Selesai
        // Booking Sari (L2M2) sengaja belum diulas: dipakai untuk demo menulis ulasan langsung.
        $this->buatBooking($sari, $lapangan[0], $kemarin, 19, 2, Booking::STATUS_SELESAI, 'L2M2', $konfirmasi);

        // Selesai + sudah diulas. Rating dan jumlah ulasan lapangan sudah diisi di data lapangan,
        // jadi tidak dihitung ulang di sini.
        $duaHariLalu = $hariIni->copy()->subDays(2);
        $tigaHariLalu = $hariIni->copy()->subDays(3);

        $this->buatUlasan(
            $this->buatBooking($andi, $lapangan[0], $duaHariLalu, 17, 2, Booking::STATUS_SELESAI, 'N4P4', $konfirmasi),
            5,
            'Lantai interlock-nya rata dan lampunya terang. Cocok sekali untuk main malam bareng tim.'
        );
        $this->buatUlasan(
            $this->buatBooking($budi, $lapangan[0], $tigaHariLalu, 20, 2, Booking::STATUS_SELESAI, 'Q6R6', $konfirmasi),
            4,
            'Fasilitas lengkap dan ruang ganti bersih. Parkir agak penuh di jam ramai.'
        );
        $this->buatUlasan(
            $this->buatBooking($andi, $lapangan[1], $duaHariLalu, 19, 1, Booking::STATUS_SELESAI, 'S8T8', $konfirmasi),
            5,
            'Atapnya bikin tetap nyaman waktu hujan. Pelayanan ramah, pasti booking lagi.'
        );
    }

    private function buatUlasan(Booking $booking, int $rating, string $komentar): Review
    {
        return Review::create([
            'user_id' => $booking->user_id,
            'field_id' => $booking->field_id,
            'booking_id' => $booking->id,
            'rating' => $rating,
            'comment' => $komentar,
        ]);
    }

    private function buatUser(string $name, string $email, string $phone, string $role): User
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => self::DEMO_PASSWORD,
        ]);
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        return $user;
    }

    private function buatBooking(
        User $user,
        Field $field,
        Carbon $tanggal,
        int $jamMulai,
        int $durasi,
        string $status,
        string $akhiranKode,
        array $tambahan = [],
    ): Booking {
        return Booking::create(array_merge([
            'code' => 'GA-'.$tanggal->format('dm').'-'.$akhiranKode,
            'user_id' => $user->id,
            'field_id' => $field->id,
            'date' => $tanggal->toDateString(),
            'start_hour' => $jamMulai,
            'duration' => $durasi,
            'price_per_hour' => $field->price_per_hour,
            'total' => $field->price_per_hour * $durasi,
            'status' => $status,
        ], $tambahan));
    }

    /** @return array<int, array<string, mixed>> */
    private function dataLapangan(): array
    {
        return [
            [
                'name' => 'Garuda Arena VIP', 'type' => Field::TYPE_INTERLOCK, 'price_per_hour' => 180000,
                'location' => 'Arena Utama Lantai 1',
                'description' => 'Lapangan interlock premium di lantai utama dengan pencahayaan LED profesional dan sirkulasi udara optimal. Sangat nyaman untuk pertandingan kompetitif maupun latihan rutin tim Anda.',
                'size' => '25 x 15 m', 'capacity' => '10 pemain',
                'facilities' => ['Free Wi-Fi', 'Shower Air Panas', 'AC Central', 'Locker Room', 'Parkir Luas'],
                'image' => 'images/2-venue-interlock.jpg', 'rating' => 4.6, 'reviews_count' => 16, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Merdeka Arena', 'type' => Field::TYPE_RUMPUT, 'price_per_hour' => 150000,
                'location' => 'Arena Outdoor Covered',
                'description' => 'Arena rumput sintetis berkualitas tinggi dengan atap pelindung semi-outdoor yang sejuk. Menghadirkan sensasi lapangan terbuka tanpa terhalang cuaca panas atau hujan.',
                'size' => '40 x 20 m', 'capacity' => '14 pemain',
                'facilities' => ['Parkir Luas', 'Kantin & Cafe', 'Musala', 'Pencahayaan Malam'],
                'image' => 'images/3-venue-rumput.jpg', 'rating' => 4.7, 'reviews_count' => 18, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Champions Court', 'type' => Field::TYPE_VINYL, 'price_per_hour' => 200000,
                'location' => 'Arena Indoor Lantai 2',
                'description' => 'Lapangan lantai vinyl kelas profesional di lantai 2 indoor ber-AC dengan fasilitas lengkap. Dilengkapi tribun penonton dan papan skor digital untuk turnamen bergengsi.',
                'size' => '38 x 20 m', 'capacity' => '14 pemain',
                'facilities' => ['Locker Room', 'Live Streaming Ready', 'Tribun Penonton', 'Papan Skor', 'AC Central'],
                'image' => 'images/4-venue-vinyl.jpg', 'rating' => 4.8, 'reviews_count' => 14, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Rencong Arena', 'type' => Field::TYPE_INTERLOCK, 'price_per_hour' => 140000,
                'location' => 'Gedung B Lantai 1',
                'description' => 'Lapangan interlock ekonomis namun tetap berkualitas tinggi di Gedung B. Pilihan favorit komunitas untuk sparing santai dengan permukaan yang sangat kesat dan aman.',
                'size' => '25 x 15 m', 'capacity' => '10 pemain',
                'facilities' => ['Free Wi-Fi', 'Parkir Luas', 'Kantin & Cafe'],
                'image' => 'images/9-lapangan-interlock-biru-hijau.jpg', 'rating' => 4.5, 'reviews_count' => 11, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Seulawah Court', 'type' => Field::TYPE_RUMPUT, 'price_per_hour' => 130000,
                'location' => 'Zona Outdoor Timur',
                'description' => 'Lapangan rumput sintetis outdoor di sisi timur kompleks dengan angin sepoi-sepoi alami. Sangat cocok untuk sesi latihan sore hari bersama rekan-rekan komunitas.',
                'size' => '30 x 18 m', 'capacity' => '12 pemain',
                'facilities' => ['Kantin & Cafe', 'Musala', 'Parkir Luas'],
                'image' => 'images/10-lapangan-rumput-outdoor.jpg', 'rating' => 4.6, 'reviews_count' => 9, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Lampuuk Turf', 'type' => Field::TYPE_RUMPUT, 'price_per_hour' => 160000,
                'location' => 'Zona Outdoor Barat',
                'description' => 'Lapangan rumput sintetis standar luas di Zona Barat dengan lampu sorot terang di malam hari. Dilengkapi fasilitas shower air panas dan kantin luas di dekat area lapangan.',
                'size' => '40 x 20 m', 'capacity' => '14 pemain',
                'facilities' => ['Parkir Luas', 'Shower Air Panas', 'Pencahayaan Malam', 'Kantin & Cafe'],
                'image' => 'images/12-lapangan-outdoor-senja.jpg', 'rating' => 4.7, 'reviews_count' => 13, 'status' => Field::STATUS_NONAKTIF,
            ],
            [
                'name' => 'Peunayong Arena', 'type' => Field::TYPE_VINYL, 'price_per_hour' => 220000,
                'location' => 'Arena Indoor Lantai 3',
                'description' => 'Arena vinyl eksklusif di lantai 3 dengan standar premium dan fasilitas siaran langsung. Menawarkan kenyamanan maksimal bagi pemain profesional maupun korporat.',
                'size' => '40 x 22 m', 'capacity' => '14 pemain',
                'facilities' => ['Tribun Penonton', 'Locker Room', 'Papan Skor', 'AC Central', 'Live Streaming Ready'],
                'image' => 'images/11-lapangan-vinyl-biru.jpg', 'rating' => 4.9, 'reviews_count' => 10, 'status' => Field::STATUS_AKTIF,
            ],
            [
                'name' => 'Krueng Arena', 'type' => Field::TYPE_INTERLOCK, 'price_per_hour' => 120000,
                'location' => 'Gedung C Lantai 1',
                'description' => 'Lapangan interlock ramah di kantong dengan kualitas permukaan standar turnamen. Menjadi andalan para pelajar dan mahasiswa untuk menyalurkan hobi futsal mereka.',
                'size' => '25 x 15 m', 'capacity' => '10 pemain',
                'facilities' => ['Free Wi-Fi', 'Musala', 'Parkir Luas'],
                'image' => 'images/13-lapangan-indoor-pertandingan.jpg', 'rating' => 4.4, 'reviews_count' => 7, 'status' => Field::STATUS_AKTIF,
            ],
        ];
    }
}

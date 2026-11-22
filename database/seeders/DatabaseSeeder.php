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
        $admin = $this->buatUser('Admin Garuda', 'garuda@gmail.com', '081200000001', User::ROLE_ADMIN, 'garuda123');
        $andi = $this->buatUser('Andi Pratama', 'andi@garudaarena.test', '081200000002', User::ROLE_PELANGGAN);
        $sari = $this->buatUser('Sari Wulandari', 'sari@garudaarena.test', '081200000003', User::ROLE_PELANGGAN);
        $budi = $this->buatUser('Budi Santoso', 'budi@garudaarena.test', '081200000004', User::ROLE_PELANGGAN);
        $rina = $this->buatUser('Rina Kusuma', 'rina@garudaarena.test', '081200000005', User::ROLE_PELANGGAN);
        $dewi = $this->buatUser('Dewi Lestari', 'dewi@garudaarena.test', '081200000006', User::ROLE_PELANGGAN);
        $fajar = $this->buatUser('Fajar Nugroho', 'fajar@garudaarena.test', '081200000007', User::ROLE_PELANGGAN);

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

        // Selesai + sudah diulas. Rating dan jumlah ulasan semua lapangan dihitung ulang
        // dari tabel reviews di akhir run() (hitungUlangRating), jadi angkanya selalu cocok.
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

        // Ulasan demo tambahan: tiap ulasan punya booking selesai sendiri (satu ulasan per booking).
        $pengulas = [$andi, $sari, $budi, $rina, $dewi, $fajar];
        $nomor = 0;

        foreach ($this->ulasanTambahan() as $indeks => $daftar) {
            foreach ($daftar as [$rating, $komentar]) {
                $nomor++;
                $this->buatUlasanTambahan($lapangan[$indeks], $pengulas[$nomor % count($pengulas)], $rating, $komentar, $nomor);
            }
        }

        $this->buatAkunDemo($admin, $lapangan);

        $this->hitungUlangRating();
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

    private function buatUlasanTambahan(Field $field, User $user, int $rating, string $komentar, int $nomor): void
    {
        $tanggal = Carbon::today()->subDays(3 + $nomor);

        $booking = $this->buatBooking(
            $user,
            $field,
            $tanggal,
            8 + ($nomor * 3) % 14,
            1,
            Booking::STATUS_SELESAI,
            'U'.str_pad((string) $nomor, 3, '0', STR_PAD_LEFT),
        );

        $waktu = $tanggal->copy()->setTime(23, 0);

        $this->buatUlasan($booking, $rating, $komentar)
            ->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])
            ->save();
    }

    /** Rating dan jumlah ulasan tiap lapangan dihitung dari tabel reviews. */
    private function hitungUlangRating(): void
    {
        foreach (Field::all() as $field) {
            $ulasan = Review::query()->where('field_id', $field->id);
            $jumlah = $ulasan->count();

            $field->update([
                'reviews_count' => $jumlah,
                'rating' => $jumlah > 0 ? round((float) $ulasan->avg('rating'), 1) : 0,
            ]);
        }
    }

    /**
     * Kunci = urutan lapangan di dataLapangan() (0 VIP, 1 Merdeka, 2 Champions, 3 Rencong,
     * 4 Seulawah, 5 Lampuuk, 6 Peunayong, 7 Krueng). Nilai = daftar [rating, komentar].
     *
     * @return array<int, array<int, array{0:int, 1:string}>>
     */
    private function ulasanTambahan(): array
    {
        return [
            0 => [
                [5, 'Pencahayaan LED-nya terang dan lantainya tidak licin. Mainnya jadi lebih nyaman.'],
                [4, 'AC dan sirkulasi udaranya enak, tidak pengap walau main dua jam.'],
                [5, 'Shower air panasnya sangat membantu setelah main malam.'],
                [4, 'Lapangan bagus, hanya antrean kantin agak lama di akhir pekan.'],
                [5, 'Sudah beberapa kali sewa di sini dan kualitasnya selalu terjaga.'],
                [3, 'Lapangannya bagus, tetapi parkir cukup penuh saat jam ramai.'],
            ],
            1 => [
                [5, 'Rumputnya empuk, aman untuk sliding tackle. Tim kami puas.'],
                [4, 'Atap pelindungnya membuat main siang tidak terlalu panas.'],
                [5, 'Kantinnya enak dan harganya bersahabat.'],
                [4, 'Tempatnya luas dan parkirnya lega, cocok untuk komunitas.'],
            ],
            2 => [
                [5, 'Lantai vinyl-nya grip-nya pas, bola mengalir mulus.'],
                [5, 'Tribun dan papan skornya bikin sparing terasa seperti turnamen.'],
                [4, 'Ruang loker bersih dan lengkap, AC-nya dingin.'],
                [5, 'Pernah dipakai untuk turnamen kantor dan semuanya lancar.'],
                [4, 'Fasilitas live streaming-nya berguna untuk merekam pertandingan.'],
                [5, 'Salah satu lapangan terbaik yang pernah kami pakai.'],
            ],
            3 => [
                [4, 'Harga bersahabat dengan permukaan lantai yang kesat dan aman.'],
                [5, 'Cocok untuk sparing santai bersama teman kuliah.'],
                [4, 'Wi-Fi-nya lancar, kantinnya buka sampai malam.'],
            ],
            4 => [
                [5, 'Anginnya sejuk, enak untuk latihan sore hari.'],
                [4, 'Rumputnya terawat, musalanya bersih dan dekat dari lapangan.'],
                [4, 'Pelayanan ramah, jadwal booking tepat waktu.'],
            ],
            5 => [
                [5, 'Lampu sorotnya terang, malam hari tetap nyaman bermain.'],
                [4, 'Shower air panasnya enak dan area kantinnya luas.'],
            ],
            6 => [
                [5, 'Standar premium, lantainya sangat rapi dan nyaman untuk pemain profesional.'],
                [5, 'Kami pakai untuk pertandingan antar perusahaan dan hasilnya memuaskan.'],
                [4, 'Fasilitas lengkap, hanya harga sedikit lebih tinggi.'],
                [5, 'Siaran langsungnya berjalan lancar, penonton ikut senang.'],
            ],
            7 => [
                [4, 'Murah tetapi kualitas lantainya tetap bagus untuk mahasiswa.'],
                [5, 'Favorit kami untuk main rutin tiap pekan.'],
                [4, 'Tempatnya sederhana, bersih, dan mudah dijangkau.'],
            ],
        ];
    }

    private function buatUser(string $name, string $email, string $phone, string $role, ?string $sandi = null): User
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $sandi ?? self::DEMO_PASSWORD,
        ]);
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        return $user;
    }

    /**
     * Empat akun demo untuk presentasi, masing-masing dengan riwayat booking dan ulasan sendiri.
     * Baris riwayat: [indeks lapangan, selisih hari, jam mulai, durasi, status, akhiran kode, ulasan [rating, komentar] atau null].
     * Tanggal -1..-3 dan +4..+5 dipilih agar tidak bentrok dengan booking demo lain.
     *
     * @param  array<int, Field>  $lapangan
     */
    private function buatAkunDemo(User $admin, array $lapangan): void
    {
        $hariIni = Carbon::today();
        $konfirmasi = ['verified_by' => $admin->id, 'verified_at' => now()];

        $akun = [
            ['Azkal', 'azkal@gmail.com', '081200000008', 'azkal123', [
                [4, -1, 19, 2, Booking::STATUS_SELESAI, 'AZ01', [5, 'Rumputnya terawat dan anginnya sejuk. Main sore jadi tidak cepat capek.']],
                [7, -2, 18, 1, Booking::STATUS_SELESAI, 'AZ02', [4, 'Harganya terjangkau dan lantainya masih bagus. Cocok untuk latihan rutin.']],
                [6, 4, 20, 2, Booking::STATUS_DIKONFIRMASI, 'AZ03', null],
                [2, 5, 19, 1, Booking::STATUS_MENUNGGU_VERIFIKASI, 'AZ04', null],
                [3, 4, 18, 2, Booking::STATUS_MENUNGGU_PEMBAYARAN, 'AZ05', null],
            ]],
            ['Furqan', 'furqan@gmail.com', '081200000009', 'furqan123', [
                [2, -1, 20, 2, Booking::STATUS_SELESAI, 'FQ01', [5, 'Tribun dan papan skornya bikin sparing terasa seperti pertandingan sungguhan.']],
                [3, -3, 16, 2, Booking::STATUS_SELESAI, 'FQ02', [4, 'Lantai interlock-nya kesat dan aman. Kantinnya buka sampai malam.']],
                [0, 4, 19, 2, Booking::STATUS_DIKONFIRMASI, 'FQ03', null],
                [4, 5, 17, 1, Booking::STATUS_MENUNGGU_PEMBAYARAN, 'FQ04', null],
            ]],
            ['Fandi', 'fandi@gmail.com', '081200000010', 'fandi123', [
                [6, -2, 19, 2, Booking::STATUS_SELESAI, 'FD01', [5, 'Lantai vinyl-nya rapi sekali, bola mengalir mulus. Layak untuk pertandingan antar tim.']],
                [3, -2, 20, 1, Booking::STATUS_SELESAI, 'FD02', [4, 'Wi-Fi lancar dan pelayanannya ramah. Jadwal booking tepat waktu.']],
                [1, 4, 20, 2, Booking::STATUS_MENUNGGU_VERIFIKASI, 'FD03', null],
                [7, 5, 19, 2, Booking::STATUS_DIKONFIRMASI, 'FD04', null],
            ]],
            ['Fadel', 'fadel@gmail.com', '081200000011', 'fadel123', [
                [4, -3, 17, 2, Booking::STATUS_SELESAI, 'FL01', [5, 'Musalanya bersih dan dekat dari lapangan. Tempatnya nyaman untuk main bareng keluarga.']],
                [7, -3, 20, 1, Booking::STATUS_SELESAI, 'FL02', [4, 'Sederhana tetapi bersih, dan mudah dijangkau dari kampus.']],
                [2, 4, 18, 2, Booking::STATUS_DIKONFIRMASI, 'FL03', null],
                [0, 5, 20, 2, Booking::STATUS_MENUNGGU_PEMBAYARAN, 'FL04', null],
                [1, 5, 16, 1, Booking::STATUS_DIBATALKAN, 'FL05', null],
            ]],
        ];

        foreach ($akun as [$nama, $email, $telepon, $sandi, $riwayat]) {
            $user = $this->buatUser($nama, $email, $telepon, User::ROLE_PELANGGAN, $sandi);

            foreach ($riwayat as [$indeks, $selisih, $jam, $durasi, $status, $kode, $ulasan]) {
                $tanggal = $hariIni->copy()->addDays($selisih);

                $tambahan = match ($status) {
                    Booking::STATUS_SELESAI, Booking::STATUS_DIKONFIRMASI => $konfirmasi,
                    Booking::STATUS_MENUNGGU_VERIFIKASI => ['proof_name' => 'bukti-demo.jpg', 'proof_submitted_at' => now()],
                    Booking::STATUS_MENUNGGU_PEMBAYARAN => ['expires_at' => now()->addMinutes((int) config('garuda.pay_window_minutes'))],
                    Booking::STATUS_DIBATALKAN => ['cancelled_reason' => 'Dibatalkan pelanggan'],
                    default => [],
                };

                $booking = $this->buatBooking($user, $lapangan[$indeks], $tanggal, $jam, $durasi, $status, $kode, $tambahan);

                if ($ulasan !== null) {
                    [$rating, $komentar] = $ulasan;
                    $waktu = $tanggal->copy()->setTime(23, 0);

                    $this->buatUlasan($booking, $rating, $komentar)
                        ->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])
                        ->save();
                }
            }
        }
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

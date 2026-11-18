<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportAdminTest extends TestCase
{
    use RefreshDatabase;

    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Jumat, 20 Nov 2026. Minggu ini = 16-22 Nov.
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
    }

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatLapangan(string $nama, string $jenis, int $harga): Field
    {
        return Field::create([
            'name' => $nama,
            'type' => $jenis,
            'price_per_hour' => $harga,
            'location' => 'Arena Utama',
            'description' => 'Lapangan uji.',
            'size' => '25 x 15 m',
            'capacity' => '10 pemain',
            'facilities' => ['Kantin'],
            'image' => 'images/2-venue-interlock.jpg',
            'rating' => 4.5,
            'reviews_count' => 10,
            'status' => Field::STATUS_AKTIF,
        ]);
    }

    private function buatBooking(User $pemilik, Field $lapangan, string $status, string $tanggal, int $jam, int $durasi, int $total): Booking
    {
        $this->urut++;

        return Booking::create([
            'code' => 'GA-LAP-'.$this->urut,
            'user_id' => $pemilik->id,
            'field_id' => $lapangan->id,
            'date' => $tanggal,
            'start_hour' => $jam,
            'duration' => $durasi,
            'price_per_hour' => intdiv($total, $durasi),
            'total' => $total,
            'status' => $status,
        ]);
    }

    private function isiData(): void
    {
        $a = $this->buatLapangan('Garuda Arena VIP', Field::TYPE_INTERLOCK, 180000);
        $b = $this->buatLapangan('Garuda Arena Rumput', Field::TYPE_RUMPUT, 100000);
        $p = User::factory()->create();

        $this->buatBooking($p, $a, Booking::STATUS_DIKONFIRMASI, '2026-11-20', 18, 1, 180000);
        $this->buatBooking($p, $b, Booking::STATUS_SELESAI, '2026-11-18', 18, 2, 200000);
        $this->buatBooking($p, $a, Booking::STATUS_MENUNGGU_VERIFIKASI, '2026-11-20', 20, 1, 180000);
        $this->buatBooking($p, $a, Booking::STATUS_DIBATALKAN, '2026-11-19', 12, 1, 180000);
        $this->buatBooking($p, $b, Booking::STATUS_SELESAI, '2026-10-15', 9, 1, 100000);
        $this->buatBooking($p, $a, Booking::STATUS_DIKONFIRMASI, '2026-11-02', 18, 1, 180000);
    }

    private function kartu(array $kartu, string $label): ?array
    {
        return collect($kartu)->firstWhere('label', $label);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/laporan')->assertRedirect(route('login'));
        $this->get('/admin/laporan/csv')->assertRedirect(route('login'));
    }

    public function test_pelanggan_mendapat_403(): void
    {
        $pelanggan = User::factory()->create();

        $this->actingAs($pelanggan)->get('/admin/laporan')->assertForbidden();
        $this->actingAs($pelanggan)->get('/admin/laporan/csv')->assertForbidden();
    }

    public function test_bulan_ini_menghitung_hanya_dikonfirmasi_dan_selesai(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=bulan&tanggal=2026-11-20')
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Rp 560.000')
            ->assertViewHas('kartu', function (array $kartu) {
                return $this->kartu($kartu, 'Booking terverifikasi')['nilai'] === '3'
                    && $this->kartu($kartu, 'Pendapatan')['nilai'] === 'Rp 560.000'
                    && $this->kartu($kartu, 'Jam terpakai')['nilai'] === '4 jam'
                    && $this->kartu($kartu, 'Rata-rata per booking')['nilai'] === 'Rp 186.667'
                    && $this->kartu($kartu, 'Lapangan terlaris')['nilai'] === 'Garuda Arena VIP'
                    && $this->kartu($kartu, 'Jam tersibuk')['nilai'] === '18.00'
                    && $this->kartu($kartu, 'Pembatalan')['nilai'] === '1';
            });
    }

    public function test_perbandingan_dengan_bulan_sebelumnya(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=bulan&tanggal=2026-11-20')
            ->assertViewHas('kartu', function (array $kartu) {
                return str_contains($this->kartu($kartu, 'Pendapatan')['catatan'], '+460%');
            });
    }

    public function test_minggu_menghitung_tujuh_hari(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=minggu&tanggal=2026-11-20')
            ->assertOk()
            ->assertSee('Rp 380.000')
            ->assertViewHas('dari', '2026-11-16')
            ->assertViewHas('sampai', '2026-11-22');
    }

    public function test_tahun_mencakup_satu_tahun_penuh(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=tahun&tanggal=2026-11-20')
            ->assertOk()
            ->assertViewHas('dari', '2026-01-01')
            ->assertViewHas('sampai', '2026-12-31');
    }

    public function test_kustom_memakai_rentang_dari_dan_sampai(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=kustom&dari=2026-11-18&sampai=2026-11-20')
            ->assertOk()
            ->assertSee('Rp 380.000')
            ->assertViewHas('dari', '2026-11-18')
            ->assertViewHas('sampai', '2026-11-20');
    }

    public function test_kustom_dengan_dari_setelah_sampai_dirapikan(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=kustom&dari=2026-11-20&sampai=2026-11-10')
            ->assertOk()
            ->assertViewHas('sampai', '2026-11-20')
            ->assertViewHas('dari', '2026-11-20');
    }

    public function test_laporan_tidak_menampilkan_grafik(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=bulan&tanggal=2026-11-20')
            ->assertOk()
            ->assertViewMissing('grafik')
            ->assertDontSee('Booking per hari');
    }

    public function test_masukan_tidak_valid_diabaikan(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=ngawur&tanggal=2026-02-31')
            ->assertOk()
            ->assertViewHas('periode', 'bulan');
    }

    public function test_tabel_per_lapangan_memuat_semua_lapangan(): void
    {
        $this->isiData();

        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan?periode=bulan&tanggal=2026-11-20')
            ->assertSee('Garuda Arena VIP')
            ->assertSee('Garuda Arena Rumput')
            ->assertViewHas('baris', fn ($baris) => $baris->count() === 2);
    }

    public function test_sidebar_memuat_menu_laporan(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan')
            ->assertSee(route('admin.reports.index'));
    }

    public function test_csv_berisi_bom_pemisah_titik_koma_dan_angka(): void
    {
        $this->isiData();

        $respons = $this->actingAs($this->buatAdmin())
            ->get('/admin/laporan/csv?periode=bulan&tanggal=2026-11-20')
            ->assertOk();

        $this->assertStringContainsString('text/csv', $respons->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $respons->headers->get('Content-Disposition'));

        $isi = $respons->getContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $isi);
        $this->assertStringContainsString('Total booking;3', $isi);
        $this->assertStringContainsString('Pendapatan (Rp);560000', $isi);
        $this->assertStringContainsString('Jam terpakai;4', $isi);
        $this->assertStringContainsString('Garuda Arena VIP;', $isi);
        $this->assertStringContainsString(';2;2;180000;360000', $isi);
    }
}

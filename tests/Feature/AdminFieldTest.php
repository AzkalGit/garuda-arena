<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminFieldTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
        Storage::fake('public');
    }

    private function buatAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function buatLapangan(array $lain = []): Field
    {
        return Field::create(array_merge([
            'name' => 'Lapangan Uji',
            'type' => Field::TYPE_INTERLOCK,
            'price_per_hour' => 150000,
            'location' => 'Gedung A',
            'description' => 'Lapangan uji.',
            'size' => '25 x 15 m',
            'capacity' => '10 pemain',
            'facilities' => ['Kantin'],
            'image' => 'images/2-venue-interlock.jpg',
            'rating' => 4.5,
            'reviews_count' => 5,
            'status' => Field::STATUS_AKTIF,
        ], $lain));
    }

    private function dataValid(array $lain = []): array
    {
        return array_merge([
            'name' => 'Lapangan Baru',
            'type' => Field::TYPE_VINYL,
            'price_per_hour' => 175000,
            'location' => 'Gedung D Lantai 1',
            'description' => 'Lapangan baru untuk uji.',
            'size' => '30 x 18 m',
            'capacity' => '12 pemain',
            'facilities' => 'Parkir Luas, Musala',
            'status' => Field::STATUS_AKTIF,
        ], $lain);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.fields.index'))->assertRedirect(route('login'));
    }

    public function test_pelanggan_ditolak_di_semua_rute_admin_lapangan(): void
    {
        $pelanggan = User::factory()->create();
        $lapangan = $this->buatLapangan();

        $this->actingAs($pelanggan)->get(route('admin.fields.index'))->assertForbidden();
        $this->actingAs($pelanggan)->get(route('admin.fields.create'))->assertForbidden();
        $this->actingAs($pelanggan)->post(route('admin.fields.store'), $this->dataValid())->assertForbidden();
        $this->actingAs($pelanggan)->get(route('admin.fields.edit', $lapangan))->assertForbidden();
        $this->actingAs($pelanggan)->put(route('admin.fields.update', $lapangan), $this->dataValid())->assertForbidden();
        $this->actingAs($pelanggan)->post(route('admin.fields.status', $lapangan))->assertForbidden();

        $this->assertSame(1, Field::count());
        $this->assertSame('Lapangan Uji', $lapangan->fresh()->name);
        $this->assertSame(Field::STATUS_AKTIF, $lapangan->fresh()->status);
    }

    public function test_admin_melihat_daftar_dan_formulir(): void
    {
        $admin = $this->buatAdmin();
        $this->buatLapangan();

        $this->actingAs($admin)->get(route('admin.fields.index'))
            ->assertOk()->assertSee('Lapangan Uji')->assertSee('Kelola Lapangan');
        $this->actingAs($admin)->get(route('admin.fields.create'))->assertOk()->assertSee('Tambah Lapangan');
    }

    public function test_filter_status_dan_pencarian(): void
    {
        $admin = $this->buatAdmin();
        $this->buatLapangan(['name' => 'Alfa Arena', 'location' => 'Zona Timur']);
        $this->buatLapangan(['name' => 'Beta Arena', 'location' => 'Zona Barat', 'status' => Field::STATUS_NONAKTIF]);

        $this->actingAs($admin)->get(route('admin.fields.index', ['status' => 'nonaktif']))
            ->assertSee('Beta Arena')->assertDontSee('Alfa Arena');

        $this->actingAs($admin)->get(route('admin.fields.index', ['q' => 'Timur']))
            ->assertSee('Alfa Arena')->assertDontSee('Beta Arena');

        $this->actingAs($admin)->get(route('admin.fields.index', ['status' => 'ngawur']))
            ->assertOk()->assertSee('Alfa Arena')->assertSee('Beta Arena');
    }

    public function test_admin_menambah_lapangan_dengan_foto(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->post(route('admin.fields.store'), $this->dataValid([
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]))->assertRedirect(route('admin.fields.index'))
            ->assertSessionHas('admin_pesan');

        $field = Field::where('name', 'Lapangan Baru')->firstOrFail();
        $this->assertSame(Field::TYPE_VINYL, $field->type);
        $this->assertSame(175000, $field->price_per_hour);
        $this->assertSame(['Parkir Luas', 'Musala'], $field->facilities);
        $this->assertSame(0, $field->reviews_count);
        $this->assertStringStartsWith('storage/lapangan/', $field->image);
        Storage::disk('public')->assertExists(Str::after($field->image, 'storage/'));
    }

    public function test_tambah_tanpa_foto_memakai_gambar_bawaan(): void
    {
        $this->actingAs($this->buatAdmin())->post(route('admin.fields.store'), $this->dataValid())
            ->assertRedirect(route('admin.fields.index'));

        $this->assertSame('images/2-venue-interlock.jpg', Field::where('name', 'Lapangan Baru')->firstOrFail()->image);
    }

    public function test_fasilitas_dirapikan_tanpa_duplikat(): void
    {
        $this->actingAs($this->buatAdmin())->post(route('admin.fields.store'), $this->dataValid([
            'facilities' => ' Musala , Parkir,, Musala ,  ',
        ]));

        $this->assertSame(['Musala', 'Parkir'], Field::where('name', 'Lapangan Baru')->firstOrFail()->facilities);
    }

    public function test_validasi_menolak_data_salah(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->post(route('admin.fields.store'), $this->dataValid([
            'type' => 'beton',
            'price_per_hour' => 500,
            'name' => '',
        ]))->assertSessionHasErrors(['type', 'price_per_hour', 'name']);

        $this->actingAs($admin)->post(route('admin.fields.store'), $this->dataValid([
            'image' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('image');

        $this->actingAs($admin)->post(route('admin.fields.store'), $this->dataValid([
            'image' => UploadedFile::fake()->image('besar.jpg')->size(3000),
        ]))->assertSessionHasErrors('image');

        $this->assertSame(0, Field::count());
    }

    public function test_nama_lapangan_harus_unik_tetapi_boleh_sama_saat_mengubah_diri_sendiri(): void
    {
        $admin = $this->buatAdmin();
        $lapangan = $this->buatLapangan();

        $this->actingAs($admin)->post(route('admin.fields.store'), $this->dataValid(['name' => 'Lapangan Uji']))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->put(route('admin.fields.update', $lapangan), $this->dataValid(['name' => 'Lapangan Uji']))
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.fields.index'));
    }

    public function test_admin_mengubah_data_tanpa_mengganti_foto(): void
    {
        $lapangan = $this->buatLapangan();

        $this->actingAs($this->buatAdmin())->put(route('admin.fields.update', $lapangan), $this->dataValid())
            ->assertRedirect(route('admin.fields.index'));

        $lapangan->refresh();
        $this->assertSame('Lapangan Baru', $lapangan->name);
        $this->assertSame(175000, $lapangan->price_per_hour);
        $this->assertSame('images/2-venue-interlock.jpg', $lapangan->image);
        $this->assertEquals(4.5, (float) $lapangan->rating);
        $this->assertSame(5, $lapangan->reviews_count);
    }

    public function test_mengganti_foto_menghapus_foto_unggahan_lama(): void
    {
        $lama = UploadedFile::fake()->image('lama.jpg')->store('lapangan', 'public');
        $lapangan = $this->buatLapangan(['image' => 'storage/'.$lama]);

        $this->actingAs($this->buatAdmin())->put(route('admin.fields.update', $lapangan), $this->dataValid([
            'image' => UploadedFile::fake()->image('baru.png'),
        ]))->assertRedirect(route('admin.fields.index'));

        $lapangan->refresh();
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists(Str::after($lapangan->image, 'storage/'));
    }

    public function test_gambar_bawaan_tidak_dihapus_saat_diganti(): void
    {
        $lapangan = $this->buatLapangan();

        $this->actingAs($this->buatAdmin())->put(route('admin.fields.update', $lapangan), $this->dataValid([
            'image' => UploadedFile::fake()->image('baru.jpg'),
        ]))->assertSessionDoesntHaveErrors();

        $this->assertStringStartsWith('storage/lapangan/', $lapangan->fresh()->image);
    }

    public function test_menonaktifkan_dan_mengaktifkan_lapangan_tanpa_membatalkan_booking(): void
    {
        $admin = $this->buatAdmin();
        $lapangan = $this->buatLapangan();
        $pelanggan = User::factory()->create();

        $booking = Booking::create([
            'code' => 'GA-TEST-1',
            'user_id' => $pelanggan->id,
            'field_id' => $lapangan->id,
            'date' => '2026-11-22',
            'start_hour' => 10,
            'duration' => 1,
            'price_per_hour' => 150000,
            'total' => 150000,
            'status' => Booking::STATUS_DIKONFIRMASI,
        ]);

        $this->actingAs($admin)->post(route('admin.fields.status', $lapangan))
            ->assertRedirect(route('admin.fields.index'))->assertSessionHas('admin_pesan');

        $this->assertSame(Field::STATUS_NONAKTIF, $lapangan->fresh()->status);
        $this->assertSame(Booking::STATUS_DIKONFIRMASI, $booking->fresh()->status);
        $this->get(route('lapangan.show', $lapangan))->assertNotFound();

        $this->actingAs($admin)->post(route('admin.fields.status', $lapangan));
        $this->assertSame(Field::STATUS_AKTIF, $lapangan->fresh()->status);
    }
}

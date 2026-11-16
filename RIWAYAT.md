# Riwayat Garuda Arena

## Fondasi (14 Nov 2026)
- Repo diganti nama menjadi `garuda-arena`; prototipe statis dipindah ke `prototipe-statis/` di cabang `fitur/laravel-fondasi`; `main` tetap prototipe.
- Kerangka Laravel 13 disalin ke akar, Breeze (Blade) dipasang, database MariaDB `garudaarena` terhubung.
- Locale `id` dan timezone `Asia/Jakarta` diatur dengan `LocaleTest`.
- Belum ditinjau sesi lain; belum dicoba di browser.

## Langkah A: Skema, model, seeder, layout (16 Nov 2026)

- Migrasi baru: kolom `phone` dan `role` di `users`, tabel `fields`, tabel `bookings` (kunci asing `restrict`, indeks `(field_id, date)`).
- `config/garuda.php`: `open_hour=8`, `close_hour=23`, `pay_window_minutes=60`.
- Model `Field` dan `Booking` (konstanta, relasi, cast, `Booking::endHour()`); `User` dapat `ROLE_*`, `bookings()`, `isAdmin()`. `role` sengaja tidak masuk `#[Fillable]`, jadi registrasi publik selalu `pelanggan`.
- Seeder: 8 lapangan (Lampuuk Turf = `nonaktif`, sisanya `aktif`), 1 admin, 3 pelanggan, 6 booking demo campur status tanpa bentrok. Tanggal booking dihitung dari hari seeder dijalankan.
- Foto disalin ke `public/images/`. Layout `app` dan `guest`: font Poppins/Inter, latar `gray-50`, logo hijau. Navigasi: baru merek "Garuda Arena" dan warna; tautan per peran menyusul di Langkah B.
- Tes: `FondasiTest` (8 tes). Total **35 passed**.
- Belum: uji di browser, uji mutasi (belum ada otorisasi atau cek bentrok).
- Catatan: booking demo `menunggu_verifikasi` punya `proof_name` tapi `proof_path` kosong (Langkah E harus menangani ini). Booking demo `menunggu_pembayaran` kedaluwarsa 1 jam setelah seeder; jalankan seeder ulang sebelum demo.

## Langkah B1: Katalog (16 Nov 2026) · commit 1c0ae0a

- Rute baru: `/` (nama `home`) dan `/lapangan` (nama `lapangan.index`). Rute `/dashboard` dan view `dashboard` dan `welcome` dihapus; semua redirect setelah login/registrasi/verifikasi diarahkan ke `home`.
- `HomeController` (invokable): 3 lapangan aktif unggulan, jumlah lapangan aktif, jam buka/tutup dan batas bayar dari `config/garuda.php`.
- `FieldController@index`: filter jenis, harga, dan urutan **di server** lewat parameter URL (`jenis`, `harga`, `urut`; nama sama dengan prototipe). Nilai tidak dikenal diabaikan.
- `Field`: tambahan `TYPE_LABELS`, `typeLabel()`, `priceLabel()`, `ratingLabel()`, `imageUrl()`.
- View: `home`, `fields/index`, komponen `field-card`, `layouts/footer`; `layouts/navigation` ditulis ulang (aman untuk tamu, menu per peran, `data-peran` di tombol pengguna); `layouts/app` memuat footer.
- Tidak dibawa dari prototipe: "Cek Ketersediaan Cepat", angka palsu (15K+ pemain, rating 4.9), modal booking, ikon media sosial.
- Tes: `KatalogTest` (11 tes baru); `ExampleTest` diberi `RefreshDatabase`; 3 tes Auth disesuaikan ke `home`. Hasil: **46 passed (129 assertions)**.
- Belum teruji: tampilan di browser, uji mutasi.
- Catatan alat: di terminal VS Code, `git push` bisa menggantung karena `GIT_ASKPASS` VS Code. Solusi: matikan `git.useIntegratedAskPass`, atau awali dengan `GIT_ASKPASS=`. `credential.helper store` sudah aktif; token klasik GitHub kedaluwarsa 16 Des 2026.

## Langkah B2: Detail lapangan (16 Nov 2026)

- Rute `GET /lapangan/{field}` (`lapangan.show`), metode `FieldController::show()`. Lapangan `nonaktif` memberi 404 (`abort_unless($field->isActive(), 404)`). Jam operasional diambil dari `config('garuda.open_hour'/'close_hour')`.
- View baru `fields/show.blade.php`: breadcrumb di slot header, foto, badge jenis dan rating, lokasi, deskripsi, spesifikasi (jenis, ukuran, kapasitas, jam operasional), fasilitas, kartu harga. Tombol "Booking Sekarang" sengaja nonaktif (placeholder untuk Langkah C).
- `components/field-card` kini menaut ke `route('lapangan.show', $field)` (sebelumnya `url('/lapangan/'.$id)`).
- Tes baru `DetailLapanganTest` (5 tes): tamu melihat detail aktif, nonaktif 404, id tidak ada 404, kartu aktif menaut ke detail, kartu nonaktif tanpa tautan. Hasil: <<ISI: N passed (M assertions)>>.
- Tidak dibawa dari prototipe: galeri 3 foto, ulasan, "Lapangan Serupa", simulasi slot hash 30%, JavaScript detail.
- Belum teruji di browser: tata letak dua kolom, kartu harga, layar sempit. Uji mutasi `abort_unless`: <<belum dilakukan / hasil>>.
- Commit: <<ISI hash>>.
- Catatan prototipe yang dipakai di Langkah C: slot mulai 08–22; durasi 1, 2, 3 jam; slot yang sudah lewat hari ini nonaktif; tanggal minimal hari ini; tautan booking membawa id, tanggal, jam, durasi.

## Langkah C — Booking + cek bentrok (16 Nov 2026)

**Commit:** <<HASH C>> · **Tes:** 73 passed (228 assertions) = 51 lama + 22 `BookingTest`.

- `Booking::createPending()`: `DB::transaction` + `lockForUpdate` pada baris lapangan. Cek lapangan aktif, jam operasional, jam sudah lewat, dan bentrok (`start < end_lain` dan `start_lain < end`, status bukan `dibatalkan`). Kode `GA-ddmm-XXXX` unik, harga disalin, `expires_at` = sekarang + 60 menit.
- `Booking::perbaruiStatus()`: `menunggu_pembayaran` yang lewat `expires_at` menjadi `dibatalkan` ("Waktu pembayaran habis"). Dipanggil di `FieldController::show()` dan `createPending()`.
- `Booking::petaJam()`: slot 08–22 per lapangan dan tanggal (Terisi, Lewat, tersedia). Hasilnya dipakai view.
- `StoreBookingRequest` (validasi + otorisasi lewat `BookingPolicy::create`: admin 403). `BookingController@store` (`booking.store`, di grup `auth`).
- `fields/show`: form tanggal (GET `?tanggal=`), radio jam dan durasi 1-3 jam, tanpa JavaScript. Tanggal tidak valid atau lewat diganti hari ini. Mode tampilan: tamu ("Masuk untuk Booking"), pelanggan, admin (pesan).
- Setelah booking sukses: kembali ke detail lapangan dengan kotak sukses (flash `booking_sukses`). Halaman pembayaran menyusul di D.
- Pint juga merapikan `ExampleTest.php` (baris kosong akhir file).
- **Belum dilakukan:** uji mutasi (dilewati atas keputusan pemilik), telusur browser (tampilan `fields/show`, alur klik per peran).
- Catatan: `lockForUpdate` tidak berefek di SQLite (basis tes), jadi balapan dua klik hanya terbukti lewat penalaran.

## Langkah D1: pembayaran dan unggah bukti (16 Nov 2026, <<HASH D1>>)

- **Baru:** `PaymentController` (`show`, `store`), `SubmitProofRequest`, view `payments/show`, `PaymentTest` (14 tes). Rute `pembayaran.show` (GET) dan `pembayaran.store` (POST) di grup `auth`, binding lewat kolom `code`.
- **Diubah:** `Booking` (+ `tanggalLabel()`, `tarifLabel()`, `bisaDibayar()`, `sisaMenit()`, `kirimBukti()`), `BookingPolicy` (+ `view`, `pay`), `BookingController@store` (redirect ke `pembayaran.show`, flash `booking_sukses` dihapus), `routes/web.php`, `BookingTest` (satu tes diubah, satu diganti).
- **Keputusan:** bukti hanya JPG/PNG maks. 2 MB (mengikuti prototipe, bukan PDF). Orang lain, termasuk admin, mendapat 404 (`Response::denyAsNotFound()`). `kirimBukti()` mengecek ulang status di dalam transaksi dengan `lockForUpdate`. Tanpa JavaScript: sisa waktu bayar ditulis sebagai menit dari server. Nomor rekening fiktif ditulis langsung di view.
- **Pengujian:** 87 passed (293 assertions). Tampilan halaman pembayaran sudah dilihat di browser (tahap `bayar`: foto, kode, total, sisa waktu tampil). Unggah bukti lewat browser dan tampilan layar sempit belum dilaporkan. **Uji mutasi D1 belum dilakukan.**
- **Sisa:** kotak sukses mati di `fields/show.blade.php` (baris sekitar 72-77, pakai `session('booking_sukses')`) belum dihapus.
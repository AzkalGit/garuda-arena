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
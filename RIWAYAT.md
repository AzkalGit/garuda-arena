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
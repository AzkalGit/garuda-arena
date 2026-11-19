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

Langkah CSS dan R1 (16 Nov 2026): aturan dosen: CSS terpisah dari view di public/css. garuda.css dibuat (variabel, dasar, tombol, navbar, footer, beranda) dan dimuat setelah @vite di layout app dan guest. Dua style="display: ..." Breeze (modal, dropdown) diganti x-cloak. View layouts/app, navigation, footer, home ditulis ulang dengan kelas sendiri (bahasa Indonesia, istilah umum Inggris); menu mobile memakai x-show + x-cloak. x-field-card, fields/*, payments/* masih Tailwind (R2, R3). Tailwind tetap dimuat untuk halaman Breeze. Belum diuji di browser.

### R2 — field-card dan katalog dengan kelas sendiri (`81030bc`, + `c082ffb`)
- `layouts/app.blade.php`: tambah slot `head` (`{{ $head ?? '' }}`) untuk CSS per halaman.
- `components/field-card` dan `fields/index` ditulis ulang tanpa Tailwind. Kelas kartu (`.kartu-*`, `.lencana-*`, `.tombol-hijau`, `.tombol-nonaktif`) masuk `garuda.css`; filter dan katalog di `public/css/katalog.css` (dimuat lewat `<x-slot name="head">`).
- Teks, rute, dan perilaku tidak berubah. Tidak ada tes yang memeriksa nama kelas.
- Tes: 87 passed (293 assertions), tidak berubah.
- Belum: telusur browser rinci (beranda, 4/2/1 kolom, filter, keadaan kosong, lapangan nonaktif). Blok `@media` akhir `garuda.css` sebelumnya tidak tertutup; sudah ditutup.

## R3a: detail lapangan dengan kelas sendiri (7b3ba10)
- fields/show.blade.php ditulis ulang tanpa Tailwind/style; CSS di public/css/detail.css (444 baris), dimuat lewat slot head.
- Kotak session('booking_sukses') yang mati dihapus.
- Tes: 87 passed (293 assertions). Telusur browser: belum dilaporkan.

## R3b: pembayaran dengan kelas sendiri
- payments/show.blade.php ditulis ulang tanpa Tailwind/style; CSS di public/css/pembayaran.css (377 baris), dimuat lewat slot head.
- Tombol kirim (.tombol-kirim, oranye) dan tombol kembali (.tombol-kembali, merah) memakai kelas sendiri, bukan .tombol-hijau.
- Tes: 87 passed (293 assertions). Telusur browser: belum dilaporkan.

### Langkah E1 — Admin verifikasi booking (17 Nov 2026) · 117 passed

- Rute admin (grup `admin.`, di dalam middleware `auth`): `admin.bookings.index` (GET /admin/booking, filter `?status=` dan pencarian `?q=`), `admin.bookings.bukti` (GET, unduh bukti), `admin.bookings.setujui` (POST), `admin.bookings.tolak` (POST).
- `Booking`: `bisaDiverifikasi()`, `setujui(User)`, `tolak(User, alasan)`. Keduanya memakai transaksi + `lockForUpdate` + cek ulang status. Pelanggaran status memberi `ValidationException` kunci `booking`.
- Tolak bukti: jam mulai belum lewat -> kembali ke `menunggu_pembayaran` dengan `expires_at` baru (60 menit); sudah lewat -> `dibatalkan` (`cancelled_reason` = "Bukti pembayaran ditolak: ..."). `rejection_reason` selalu terisi. Berkas bukti lama dihapus dari disk privat; `proof_path`, `proof_name`, `proof_submitted_at` dikosongkan.
- Alasan awal (default usulan "pakai `expires_at`") diganti karena `kirimBukti()` mengosongkan `expires_at`; yang dipakai adalah jam mulai main.
- `BookingPolicy`: `kelola` (daftar) dan `verify` (setujui/tolak/unduh), keduanya hanya admin (selain itu 403). `RejectBookingRequest`: `alasan` wajib, 5–255 karakter.
- Unduh bukti: bila `proof_path` kosong atau berkas tidak ada (data demo), redirect ke daftar dengan galat `booking`.
- View `admin/bookings/index` memakai ulang `riwayat.css` + `admin-booking.css`. Aksi tolak memakai kolom teks alasan di baris tabel (tanpa JavaScript).
- Tes baru: `AdminBookingTest` (13 tes). Total 104 -> 117.
- Belum dicek di browser. Belum ada: `rejection_reason` tampil di halaman pembayaran pelanggan.
## Langkah E1b — Alasan penolakan di halaman pembayaran (17 Nov 2026)
- `payments/show.blade.php`: pada tahap `bayar`, bila `rejection_reason` terisi, tampil kotak `.pembayaran-galat` berisi "Bukti pembayaran sebelumnya ditolak" dan alasannya. Tanpa CSS baru.
- `PaymentTest`: +2 tes (alasan tampil; tidak tampil bila belum pernah ditolak).
- Belum dicek browser.
## Langkah E2 — Admin kelola lapangan (17 Nov 2026)

**Hasil:** admin dapat melihat daftar lapangan (filter `?status=`, cari `?q=` pada nama/lokasi), menambah, mengubah, dan mengaktifkan/menonaktifkan lapangan. Tes: 132 passed (119 + 13 tes `AdminFieldTest`).

**File baru:** `Admin/FieldController` (index, create, store, edit, update, status), `FieldRequest`, `FieldPolicy` (`kelola`, `ubah`), `admin/fields/index|form.blade.php`, `public/css/admin-lapangan.css`, `tests/Feature/AdminFieldTest.php`.
**File diubah:** `Field.php` (`statusLabel()`, `fasilitasTeks()`, `gambarUnggahan()`), `routes/web.php` (6 rute `admin.fields.*`), `layouts/navigation.blade.php` (menu Kelola Lapangan; Panel Admin hanya menyala di `admin.bookings.*`), `KatalogTest.php` (admin dicari lewat `role`).

**Keputusan:**
- Menonaktifkan lapangan tidak membatalkan booking yang ada; hanya menutup pemesanan baru.
- Foto: JPG/PNG maks. 2 MB, disk `public` (`storage/lapangan/`), dicatat sebagai `storage/...`; tanpa foto memakai `images/2-venue-interlock.jpg`. Foto unggahan lama dihapus saat diganti; gambar bawaan tidak pernah dihapus. Butuh `php artisan storage:link`.
- Fasilitas: teks berkoma, dirapikan, tanpa duplikat, maks. 12 butir @ 40 karakter. Rating dan jumlah ulasan baru = 0 dan tidak bisa diubah dari form.
- Nama lapangan unik; tarif bilangan bulat Rp10.000–Rp5.000.000.
- Tidak ada hapus lapangan (hanya nonaktif).

**Belum teruji:** tampilan di browser (daftar cek di percakapan E2), unggah foto sungguhan, symlink storage.
## Langkah G1 (17 Nov 2026) — layout admin dengan sidebar
- Komponen baru: `x-admin-layout`, `x-admin-sisi`, `public/css/admin-layout.css`, `tests/Feature/AdminLayoutTest.php` (3 tes). Tiga view admin (`bookings/index`, `fields/index`, `fields/form`) dipindah dari `x-app-layout` ke `x-admin-layout`. Tes: 135 passed.
- Sidebar: menu hanya tampil bila rutenya ada (`Route::has`); Dashboard, Kelola User, Laporan belum ada rutenya. Menu geser di layar sempit pakai Alpine.
- Temuan pemilik (screenshot prototipe + tampilan sekarang): tautan "Panel Admin" dan "Kelola Lapangan" masih ada di navbar publik; desain panel admin, kelola booking, dan kelola lapangan dinilai jelek; sidebar harus mirip prototipe; dashboard dan laporan belum ada.
- Diputuskan di chat: laporan tidak butuh tabel baru (dihitung dari `bookings.date`); diagram SVG dihitung di server (tanpa paket JS); CSV pemisah `;` + BOM UTF-8.
- `admin-laporan.js` prototipe hanya memakai bulan berjalan; tab minggu/bulan/tahun/kustom dibuat baru.
- Belum dicek di browser oleh Claude; jawaban pemilik atas pertanyaan pola diagram belum ada (default dipakai).
## G1b: Navbar publik dan restyle panel admin (17 Nov 2026)

- Penyebab tampilan admin polos: `admin-layout.css` kosong, sedangkan CSS sidebar/topbar tertempel di `admin-booking.css` dan CSS tabel/tombol/kotak cari tidak ada. CSS dipisah ulang: `admin-layout.css` = sidebar, menu geser, topbar, avatar; `admin-booking.css` = kotak cari, tabel, tombol aksi.
- Sidebar ala prototipe: logo bulat hijau, ikon SVG inline per menu, menu aktif = kotak hijau membulat, catatan kaki "Tugas kuliah PBW – Kelompok 6". Topbar: judul halaman di kiri, avatar inisial + nama di kanan.
- Navbar publik: tautan "Panel Admin" dan "Kelola Lapangan" diganti satu tombol oranye `.tombol-panel-admin` (ke `admin.dashboard` bila rutenya ada, kalau tidak ke `admin.bookings.index`); juga di menu mobile. Gayanya di akhir `garuda.css`.
- Tes baru `NavbarAdminTest` (2 tes). Total 137 passed.
- Pelajaran: setelah menempel CSS, selalu cek `git status --short` (file harus ` M`) dan `grep -c` pada satu kelas penanda.
- Belum: kartu statistik di Kelola Lapangan, filter jenis lapangan, restyle form lapangan lebih jauh.
### G2 — Dashboard admin (17 Nov 2026)
- Rute `admin.dashboard` (`/admin`), `Admin/DashboardController` (invokable), view `admin/dashboard.blade.php`, komponen `x-grafik-batang` (SVG dihitung di server), `public/css/admin-dashboard.css`, `DashboardAdminTest` (6 tes).
- 4 kartu: booking hari ini (tanpa `dibatalkan`), menunggu verifikasi, pendapatan terverifikasi (`dikonfirmasi` + `selesai`, seluruh waktu), lapangan aktif "X dari Y".
- Grafik "Booking 7 hari terakhir" (hari ini dan 6 hari sebelumnya, tanpa `dibatalkan`); tabel 5 booking terbaru dengan tautan ke Kelola Booking.
- Tes: 143 passed. Belum diuji di browser.
## G3 — Laporan (17 Nov 2026)
- Rute `GET /admin/laporan` (`admin.reports.index`) dan `GET /admin/laporan/csv` (`admin.reports.csv`) di grup admin.
- `Admin/ReportController` (`index`, `csv`; privat `susun`, `rentang`, `tanggal`, `rupiah`, `barisCsv`). Periode lewat `?periode=` (minggu/bulan/tahun/kustom), `?tanggal=`, `?dari=`/`?sampai=`; masukan tak valid diabaikan, tanggal terbalik dirapikan.
- Hanya booking `dikonfirmasi` dan `selesai` dihitung. Ada perbandingan dengan periode sebelumnya, tabel per lapangan, dan grafik tren memakai `x-grafik-batang`.
- CSV: pemisah `;`, BOM UTF-8, ringkasan di atas dan tabel lapangan di bawah, angka tanpa format Rp.
- View `resources/views/admin/reports/index.blade.php`, CSS `public/css/admin-laporan.css`.
- Tes baru `ReportAdminTest` (12 tes). Total 155 passed.
- Kendala: route sempat belum tertempel, sehingga 12 tes gagal (404). Setelah ditambahkan, semua lulus.
- Belum dicek di browser.
### G3b: laporan tanpa grafik tren (18 Nov 2026)
- Grafik tren dibuang dari `/admin/laporan`: blok `grafik`/`grafikJudul` di `ReportController::susun()` dan panel grafik di view dihapus. Dashboard tetap punya grafik 7 hari.
- `ReportAdminTest`: tes grafik diganti pemeriksaan `dari`/`sampai`; ditambah `test_laporan_tidak_menampilkan_grafik`. Tes 155 → 156.

### G4: Kelola pengguna (18 Nov 2026)
- Rute `GET /admin/pengguna` (`admin.users.index`), `Admin/UserController::index` (invokasi `Gate::authorize('kelola', User::class)`), `UserPolicy::kelola` (admin saja, selain itu 403).
- Daftar semua user: pencarian nama/email (`?q=`), admin diurutkan di atas, kolom peran, jumlah booking (`withCount`), tanggal bergabung. Tanpa hapus dan tanpa ubah peran.
- `User::peranLabel()` dan `User::bergabungLabel()`. CSS baru `public/css/admin-pengguna.css` (`.lencana-peran*`). Sidebar menampilkan menu Pengguna otomatis lewat `Route::has`.
- `UserAdminTest` (6 tes). Tes 156 → 162 passed. Belum dicek browser.

## H0 — Logo bola, ikon sidebar solid, login/daftar (19 Nov 2026)

- **Temuan gladi F2:** pemilik membandingkan panel admin proyek dengan prototipe: logo masih lingkaran (bukan bola futsal), ikon sidebar outline (prototipe solid), urutan menu beda, logo Breeze masih tampil di halaman publik, login/daftar belum didesain. Ukuran menu ternyata sama dengan prototipe (`text-sm`, `py-3`, `w-64`), jadi CSS sidebar tidak diubah.
- **Diubah:** `components/admin-sisi.blade.php` (logo bola `$bola`, lima ikon solid SVG inline, urutan Dashboard, Lapangan, Booking, User, Laporan); `components/application-logo.blade.php` (kotak Breeze diganti bola futsal, atribut `class` pemanggil tetap diteruskan).
- **Baru:** `components/auth-layout.blade.php`, `public/css/auth.css`, `tests/Feature/AuthTampilanTest.php` (3 tes). **Ditulis ulang:** `auth/login.blade.php`, `auth/register.blade.php` (Bahasa Indonesia, galat via `@error`, tanpa akun demo, tanpa tombol sosial palsu). `layouts/guest.blade.php` sengaja tidak diubah.
- **Hasil:** tes **165 passed** (573 assertions). Jumlah `{` = `}` di `auth.css` (33). Pint merapikan satu baris kosong di akhir tes.
- **Keputusan baru:** galeri = foto saja + nama tempat; ulasan + rating dibuat (membalik "tanpa ulasan"); animasi CSS saja; ikon fasilitas dipetakan lewat kata kunci. Urutan H1 (ikon fasilitas + animasi), H2 (galeri), H3 (ulasan), F2.
- **Belum teruji:** tampilan login/daftar dan ikon sidebar baru di browser; galat login masih bahasa Inggris; email admin seeder masih `admin@g`.
- **Pelajaran:** angka `wc -l`/`grep -c` di serahan hanyalah perkiraan (`grep -c` menghitung baris, bukan kemunculan).

## H1 — Ikon fasilitas + animasi CSS (19 Nov 2026)

- **Temuan:** H1 sudah dikerjakan dan di-commit (`8e82afa`) sebelum sesi ini, tetapi belum tercatat (pola yang sama dengan G2 dan G3b). Ditemukan lewat `git status --short` kosong, tes 170 (bukan 165), dan `<x-ikon-fasilitas>` sudah dipakai di view.
- **Ada:** `components/ikon-fasilitas.blade.php` (prop `nama`; 12 ikon SVG inline dipetakan lewat kata kunci: wifi, parkir, toilet, shower, kantin, musala, ac, locker, streaming, tribun, skor, cahaya; tak dikenal = centang; atribut `data-ikon` memuat kunci). Dipakai di `field-card.blade.php` dan `fields/show.blade.php`.
- **CSS:** blok "Ikon dan animasi" di `katalog.css` (hover kartu naik + foto membesar, muncul `muncul-naik` dengan jeda bertahap, `prefers-reduced-motion`) dan `detail.css` (chip fasilitas, kartu detail muncul halus, `prefers-reduced-motion`). `garuda.css` tidak diubah.
- **Tes:** `IkonFasilitasTest` ada; total **170 passed** (587 assertions). Isi berkas tes belum dilihat sesi ini.
- **Belum teruji:** tampilan di browser (kartu `/lapangan`, chip di detail, layar sempit). Baris 59 `show.blade.php` kurang rapi (`</span>` dan `@endforeach` satu baris), tidak mempengaruhi tampilan.
- **Pelajaran:** tulis catatan serah-terima segera setelah commit langkah.

## H2 — Halaman Galeri (19 Nov 2026)

- **Baru:** `GalleryController@index` (rute `GET /galeri`, nama `galeri.index`), `resources/views/galeri/index.blade.php`, `public/css/galeri.css`, `tests/Feature/GaleriTest.php` (6 tes).
- **Diubah:** `routes/web.php` (use + rute), `layouts/navigation.blade.php` (menu "Galeri" kini ke `galeri.index`, bukan `home#gallery`; bagian `#gallery` di beranda tidak disentuh).
- **Perilaku:** hanya lapangan `aktif`; filter `?jenis=` lewat tab (nilai tak dikenal atau array diabaikan); data (nama, lokasi, jenis, foto) disiapkan di controller; klik foto = tampilan besar Alpine (`x-show` + `x-cloak`, tutup lewat tombol ×, Esc, klik latar); animasi `muncul-naik` + `prefers-reduced-motion`. Tanpa tabel baru.
- **Hasil:** tes **176 passed** (606 assertions). CSS 267 baris, `{` = `}` (42). Cek bersih view kosong.
- **Belum teruji:** tampilan browser (grid 1/2/3 kolom, tampilan besar, hover, menu aktif di navbar dan hamburger).
- **Insiden:** setelah tes pertama 41 gagal, karena `use GalleryController` sudah masuk tetapi baris rute belum; Pint lalu membuang `use` yang tak terpakai sehingga `routes/web.php` kembali ke versi lama dan navbar memanggil rute yang tidak ada. Diperbaiki dengan menempel use + rute sekaligus sebelum Pint.
- **Pelajaran:** perubahan `routes/web.php` yang terdiri dari `use` + rute harus ditempel keduanya sebelum Pint; verifikasi `grep -c` dan ` M` dulu, baru Pint dan tes.
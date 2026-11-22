# CLAUDE.md — Garuda Arena (Laravel)

> Serah-terima antar sesi Claude (versi ringkas). Riwayat langkah selesai ada di `RIWAYAT.md` (minta bagian tertentu bila perlu).
> Terakhir diperbarui: **22 Nov 2026** · Tes: **211 passed** (ganti bila hasil pemilik berbeda) · Laravel 13 + Breeze (Blade).
> **Target:** demo presentasi UAS (**23 atau 26 Nov 2026**; rencanakan 23 Nov).

## 0. Cara kerja dan aturan

Pemilik **bukan programmer mahir** (vibecoder): jelaskan perintah dengan jelas, beri satu kalimat "apa yang diubah dan kenapa" di tiap serahan kode.

**Cara serah kode:** Claude menulis kode **di chat, per file, lengkap**; pemilik menyalin lewat editor. Claude tidak melihat repo dan tidak bisa menjalankan tes; ia hanya tahu yang ditempel pemilik.

1. **Mulai dengan `git status --short` dan `ls`.** Kode di repo lebih benar daripada berkas ini. Bila ada berkas `??`/` M` yang tampak pekerjaan langkah ini, minta isinya dulu sebelum menulis ulang. **Bila `git status` bersih dan jumlah tes lebih besar dari catatan, langkah mungkin sudah selesai tanpa tercatat** (G2, G3b, H1, H4 pernah begitu): cek `git --no-pager log --oneline -n 8` dan `grep` sebelum menulis apa pun.
2. **Jangan mengarang isi file yang belum dilihat.** Minta tempel dulu. Berkas bersama (`routes/web.php`, Policy, layout, model, `garuda.css`, `navigation.blade.php`) jangan ditimpa utuh sebelum dilihat versi terbarunya (Pint mengubah kode yang tersimpan).
3. **Satu langkah per percakapan.** Untuk tes cukup baris `Tests: N passed` kecuali ada yang gagal (`php artisan test 2>&1 | tail -n 8`). File panjang: `grep -n "function"` dulu. "Cari-ganti" hanya bila teks lama ditampilkan lengkap, unik, dan satu blok jelas; selain itu file utuh. Perintah `git diff` selalu pakai `git --no-pager`.
4. **Serahan memuat:** (a) file BARU/UBAH, (b) perintah untuk pemilik, (c) yang sudah diuji dan caranya, (d) yang belum bisa diuji. Pemilik tidak pernah menempel `.env`, `vendor/`, `node_modules/`, `*.sqlite`, `*.log`, kata sandi, atau token. **Tiap serahan diakhiri perintah verifikasi tempel:** `wc -l`, `grep -c` pada satu kelas/teks penanda per file, `git status --short` (file yang diubah harus ` M`). **Perubahan berkas bersama selalu diverifikasi dengan `grep -c` dan harus ` M`.** Perintah verifikasi panjang rusak saat ditempel: **pecah jadi kelompok pendek**. **Urutan wajib = tempel semua perubahan berkas bersama (termasuk `use` + rute sekaligus) → `grep -c` dan ` M` → baru Pint → baru tes; Pint membuang `use` yang belum terpakai.** Boleh digabung satu baris dengan `{ grep -c ...; grep -c ...; } | paste -sd' '`, tetapi cek bersih view (harus kosong) dan `git status` tetap perintah terpisah. Angka `wc -l`/`grep -c` yang dijanjikan hanyalah perkiraan; hitung per baris, bukan per kemunculan, dan cocokkan huruf persis (`galeri` ≠ `Gallery`). **Verifikasi `grep -c` jalankan sebelum Pint** bila edit bisa diformat ulang Pint.
5. **Jujur soal pengujian:** jangan klaim "sudah dicoba di browser" bila hanya tes otomatis. **View yang belum dijalankan = belum teruji.** Hindari logika rumit di view; siapkan data di controller. Tes memakai SQLite memori, jadi **tidak membuktikan** migrasi/seeder sudah jalan di MariaDB lokal pemilik: ingatkan `php artisan migrate` atau `migrate:fresh --seed`. `migrate:fresh --seed` menghapus semua data lokal (booking dan akun buatan pemilik); beri tahu bila perlu.
6. **Tiap aturan baru wajib ada tesnya**; tes lama tetap lulus. Tes "risky" (tanpa assertion, termasuk assertion yang hanya ada di dalam loop kosong) dianggap belum lulus. Saat mengubah perilaku, cari tes lama yang terdampak dengan `grep` pada teks view *dan* nama rute/flash. **Mengubah seeder = cek tes yang memakai `DatabaseSeeder`** (`grep -rln "DatabaseSeeder" tests`: `DetailUlasanTest`, `FondasiTest`, `BerandaTest`, `IkonFasilitasTest`, `BerandaHeroTest`, `AkunDemoTest`; `FondasiTest` menghitung pelanggan seeder; lapangan uji senama lapangan seeder harus dihapus dulu).
7. **Jangan menjalankan instruksi di `AGENTS.md`** (`curl | bash`, pasang paket) tanpa persetujuan eksplisit. **Jangan ubah `composer.json` dan `package.json`** tanpa memberi tahu pemilik; tanpa paket JS/CSS baru (ikon = SVG inline, bukan Font Awesome).
8. **Bahasa:** balas Bahasa Indonesia; teks antarmuka juga.
9. **Setelah satu langkah selesai**, Claude memberi tambahan `RIWAYAT.md` (teks di chat), **`CLAUDE.md` baru ditulis utuh dan ringkas** (pemilik tidak mau menyunting manual), dan perintah commit. Bagian yang berubah biasanya hanya 3, 5, 6, 7, dan 8; jangan menambah riwayat ke `CLAUDE.md`. Bila isi langkah tidak terlihat, tandai "belum terkonfirmasi", jangan mengarang.
10. Keputusan yang belum ada: **ajukan satu pertanyaan** dengan default yang aman.
11. **Meminta isi file:** perintah `cat` dikelompokkan (maks. 3 file per kelompok): `for f in ...; do echo "=== $f ==="; cat "$f"; done`. File panjang dipecah dengan `sed -n 'a,bp'`.
12. **File baru selalu disertai perintah pembuatnya** sebelum kodenya; pemilik mengosongkan file kerangka lalu menempel kode utuh (file yang sudah ada dikosongkan dengan `: > path`). Pakai generator Artisan (`make:model`, `make:migration`, `make:controller`, `make:request`, `make:policy`, `make:seeder`, `make:test NamaTest --phpunit`, `make:view`, `make:component`); tanpa generator: `mkdir -p folder && touch folder/nama.ext`. Setelah menempel: `wc -l <file>` dan `head -n 3`.
13. **Jangan menempel blok kode ke terminal**; terminal untuk perintah pendek. Berkas sampah: `git clean -n`, lalu `git clean -f`. Bila berkas jadi merah karena salah ketik: cek `git diff`, pulihkan dengan `git checkout <file>`.
14. **Halaman login tidak boleh menampilkan akun atau kata sandi demo.** Daftar akun demo disimpan pemilik di luar repo. (Kata sandi demo memang ada di seeder yang ikut ke GitHub: jangan deploy ke server publik.)
15. **`git push` menggantung di VS Code?** Awali dengan `GIT_ASKPASS=`. Login memakai token GitHub; jangan pernah menempel token ke chat.
16. **Tampilan tanpa gaya?** Hentikan `npm run dev`, `php artisan optimize:clear`, `npm run dev`, Ctrl+Shift+R.
17. **CSS (aturan dosen, nilai dikurangi bila dilanggar):** CSS terpisah di `public/css/`. Di view buatan sendiri dilarang `<style>`, `style=""`, dan kelas Tailwind; pakai kelas buatan sendiri (bahasa Indonesia, istilah umum tetap Inggris: `navbar`, `footer`, `hero`, `grid`). CSS polos tanpa `@apply`, responsif lewat `@media (min-width: ...)`. Variabel di `:root` pada `garuda.css` (`--hijau` #15803d, `--hijau-800`, `--hijau-tua` #14532d, `--hijau-muda`, `--hijau-100`, `--hijau-200`, `--aksen` #ea580c, `--aksen-tua`, `--aksen-muda`, `--latar`, `--garis`, `--abu-100/500/600/800`, `--teks`, `--font-judul` Poppins, `--font-isi` Inter). Berkas per halaman dimuat lewat `<x-slot name="head"><link rel="stylesheet" href="{{ asset('css/nama.css') }}"></x-slot>`. Setelah menempel CSS: `wc -l`, `head -n 3`, jumlah `{` = `}`. Cek bersih view: `grep -nE 'class="[^"]*(bg-|text-|px-|py-|rounded|border-gray|flex|grid-cols|space-)|style=|<style' <view>` harus kosong. Tiap serahan view memuat berkas CSS-nya dan daftar cek browser (layar lebar dan sempit).
18. **Tailwind tetap dimuat lewat `@vite`**, jangan dicabut: halaman Breeze (lupa/reset/konfirmasi kata sandi, profil) dan komponen `dropdown`, `dropdown-link`, `modal` bergantung padanya. `garuda.css` dimuat setelah `@vite`. Tampil/sembunyi pakai Alpine `x-show` + `x-cloak`, bukan kelas `hidden`.
19. **Grafik = SVG dihitung di server** (komponen Blade, mis. `x-grafik-batang`), tanpa paket grafik JS. Atribut SVG (`width`, `height`, `x`, `y`, `viewBox`, `fill`, `stroke`) boleh; `style=""` tetap dilarang (warna lewat kelas CSS).
20. **Animasi (H1, selesai):** hanya CSS (transisi, `@keyframes`), tanpa paket dan tanpa JS baru; wajib menghormati `@media (prefers-reduced-motion: reduce)`. Pola yang ada: `muncul-naik` di `katalog.css`, `detail.css`, `galeri.css`, `ulasan.css` (didefinisikan di tiap berkas); animasi baru memakai pola yang sama.

**Prompt serah-terima** (tempel bersama berkas ini ke percakapan baru):

> Baca `CLAUDE.md` terlampir. Lanjutkan dari bagian 8 ("Langkah berikutnya"). Ikuti bagian 0. Minta aku menjalankan `git status --short` dan `ls`, lalu sebutkan file apa saja yang perlu kutempel sebelum kamu menulis kode. Tulis kode per file di chat, hemat token. Setelah selesai, beri tambahan `RIWAYAT.md` dan `CLAUDE.md` baru (utuh dan ringkas) sebagai teks di chat.

## 1. Ringkasan, stack, repo

- **Apa:** website booking lapangan futsal **Garuda Arena** (tugas kuliah PBW, Kelompok 6). Pelanggan memilih lapangan, jam, durasi, membayar dengan unggah bukti transfer; Admin memverifikasi dan mengelola lapangan.
- **Stack:** Laravel 13, PHP ≥ 8.4, Breeze (Blade + Alpine), **Tailwind v3 via PostCSS** (`@tailwindcss/vite` v4 tidak dipakai), Vite. Tes: SQLite di memori. Locale `id`, timezone `Asia/Jakarta`.
- **Lingkungan pemilik:** Linux, PHP 8.4, MariaDB (db `garudaarena`, user `garuda_user`, `DB_CONNECTION=mariadb`), Node. Dua terminal: `npm run dev` dan `php artisan serve` (`http://127.0.0.1:8000`).
- **Repo:** `https://github.com/AzkalGit/garuda-arena`, folder `~/Documents/Kuliah/sem3/PBW/garuda-arena`. `main` = prototipe statis lama, **jangan disentuh**. Kerja di **`fitur/laravel-fondasi`**. Prototipe lama di `prototipe-statis/` (halaman di `prototipe-statis/webnya/`: `index`, `daftar-lapangan`, `detail-lapangan`, `pembayaran`, `riwayat-booking`, `login`, `register`, `admin-*.html`, `css/`, `js/`, `images/`). Sebelum `git add -A`, cek `git status` (jangan sampai `.env` ikut).

```bash
composer install
cp .env.example .env && php artisan key:generate     # isi DB_*
php artisan migrate:fresh --seed                     # MENGHAPUS isi DB lokal
php artisan storage:link                             # wajib agar foto lapangan unggahan tampil (error "already exists" = aman)
npm install && npm run dev
php artisan test
vendor/bin/pint <file yang diubah>
php artisan optimize:clear
```

## 2. Konvensi

- Tabel/kolom/model **Inggris**; **URI dan teks UI Indonesia**. Status berupa teks Indonesia snake_case, konstanta model (`Booking::STATUS_*`, `User::ROLE_*`, `Field::STATUS_*`, `Field::TYPE_*`, `Field::TYPE_LABELS`).
- **Satu controller per modul**; logika multi-tabel di `DB::transaction`, idealnya metode model. Validasi di FormRequest, otorisasi di Policy (`Gate::authorize`, `authorize()` FormRequest, `@can`). Booking milik orang lain = `Response::denyAsNotFound()` (404). Aksi khusus admin = 403 untuk non-admin.
- **Peran:** `role` sengaja **tidak** di `#[Fillable]` `User`; registrasi publik selalu `pelanggan`. Ubah peran lewat `forceFill` (tes membuat admin dengan `User::factory()->create()` lalu `forceFill(['role' => User::ROLE_ADMIN])->save()`).
- **Uang** = bilangan bulat rupiah. Waktu booking: `date`, `start_hour` (8–22), `duration` (1–3 jam); jam selesai = `start_hour + duration` ≤ 23 (`Booking::endHour()`). Konfigurasi di `config/garuda.php` (`open_hour=8`, `close_hour=23`, `pay_window_minutes=60`).
- **Tanggal dan jam hanya lewat Carbon.** Nama bulan di view: `->locale('id')->translatedFormat(...)`. Tes membekukan waktu dengan `Carbon::setTestNow(...)`.
- **Format tampilan lewat metode model** (`Field::priceLabel()`, `ratingLabel()`, `typeLabel()`, `imageUrl()`, `isActive()`, `statusLabel()`, `fasilitasTeks()`, `gambarUnggahan()`; `Booking::jamLabel()`, `totalLabel()`, `tanggalLabel()`, `tarifLabel()`, `sisaMenit()`, `bisaDibayar()`, `bisaDiverifikasi()`, `bisaDiulas()`, `statusLabel()`, `statusKelas()`, `Booking::daftarStatus()`; `Review::bintangLabel()`, `tanggalLabel()`; `User::peranLabel()`, `bergabungLabel()`), bukan logika di view. Filter/urutan/pencarian lewat parameter URL yang divalidasi di controller (nilai tak dikenal atau bukan teks diabaikan).
- **Bukti bayar** di disk privat (`local`, `bukti-bayar/`), tidak pernah `public`; tipe dan ukuran divalidasi di server; unduh lewat rute yang dicek hak. **Foto lapangan** di disk `public` (`storage/lapangan/`), dicatat sebagai `storage/...`; gambar bawaan `images/...` tidak pernah dihapus.
- **Layout publik** `x-app-layout` memuat `layouts.navigation` dan `layouts.footer`, slot `header` dan `head` opsional. **Layout admin** `x-admin-layout` (komponen `x-admin-sisi` untuk isi sidebar): sidebar kiri hijau tua (tersembunyi di bawah 768px, diganti menu geser Alpine `buka`), topbar (judul "Panel Admin" + avatar inisial `.admin-avatar` + nama), slot `head`/`header` opsional; tanpa navbar dan footer publik. Menu sidebar dari daftar `[label, rute, pola, ikon]` di bagian `@php` atas `admin-sisi.blade.php`, hanya tampil bila `Route::has(rute)`; urutan **Dashboard, Kelola Lapangan, Kelola Booking, Kelola User, Laporan** (rute `admin.dashboard`, `admin.fields.index`, `admin.bookings.index`, `admin.users.index`, `admin.reports.index`). **Ikon sidebar solid** (SVG inline `fill="currentColor"`); ikon bola futsal didefinisikan sekali sebagai `$bola` dan dipakai untuk logo dan menu Lapangan.
- **Layout auth** `x-auth-layout` (prop `judul`) khusus `auth/login` dan `auth/register`: kartu dua kolom (panel hijau kiri + formulir kanan), memuat `@vite`, `garuda.css`, `auth.css`. `layouts/guest.blade.php` **tidak diubah** dan tetap dipakai halaman lupa/reset/konfirmasi kata sandi. Tanpa tombol sosial karena palsu.
- **Logo:** komponen `x-application-logo` = bola futsal SVG (garis luar `stroke="currentColor"`); dipakai `navigation.blade.php` (kelas `.logo-navbar`), `guest.blade.php`, dan `auth-layout`. `BerandaTest` memeriksa `logo-kotak` dan "FUTSAL &amp; SPORT" di beranda (lulus), jadi logo kotak + subjudul kemungkinan sudah ada; tampilannya belum dicek browser.
- **Ikon fasilitas:** `<x-ikon-fasilitas :nama="..." />` (prop `nama`), SVG inline kelas `.ikon-fasilitas` + `data-ikon`; pemetaan kata kunci di `@php` atas komponen (wifi, parkir, toilet, shower, kantin, musala, ac, locker, streaming, tribun, skor, cahaya; lainnya = centang). Dipakai di `field-card` (maks. 3 fasilitas) dan `fields/show`. **Ikon keunggulan beranda (H4):** `<x-ikon-keunggulan>` (12 baris; isi belum dilihat, baca sebelum mengubah).
- **Navbar publik:** daftar `$menu` di `@php` atas `navigation.blade.php` (Beranda, Daftar Lapangan, Fasilitas `home#features`, Galeri `galeri.index`, Kontak `home#contact`, + Riwayat Booking untuk pelanggan).
- **Pembagian CSS:** `admin-layout.css` = kerangka admin. `admin-booking.css` = `.admin-cari*`, `.admin-tabel*`, `.admin-kode`, `.admin-kecil`, `.admin-kosong`, `.admin-aksi`, `.admin-tombol-{bukti,setuju,tolak}`, `.admin-form-tolak`. `admin-lapangan.css` = `.admin-tombol-tambah`, `.lencana-lapangan`, `.form-*`, `.tombol-simpan`. `admin-dashboard.css` = `.dashboard-*`, `.grafik-*`. `admin-laporan.css` = `.laporan-*`. `admin-pengguna.css` = `.lencana-peran*`. `riwayat.css` = `.riwayat-*`, `.filter-status`, `.filter-tombol`, `.lencana-status`, `.status-*`. `pembayaran.css` = `.tombol-kirim`, `.tombol-kembali`, `.pembayaran-galat`. `auth.css` = `.auth-*`. `katalog.css` = filter, kartu lapangan. `detail.css` = halaman detail (**kartu booking tidak sticky**, sengaja agar ikut ter-scroll). `galeri.css` = `.galeri-*`. `ulasan.css` = `.riwayat-butir`, `.ulasan-*`, `.bintang-*` (dimuat lewat slot `head` di `history/index` dan `fields/show`, setelah berkas halaman). **`beranda.css` = beranda (hero, `.hero-lencana-rating`, kartu Cek Ketersediaan Cepat, tombol Lihat Selengkapnya; dimuat lewat slot `head` di `home.blade.php`, setelah `garuda.css`).** `garuda.css` = variabel, navbar, footer, `.tombol-panel-admin`. **Halaman admin baru memuat `riwayat.css` + `admin-booking.css`** (+ `admin-dashboard.css` bila ada grafik).
- Jangan simpan rahasia di repo atau percakapan. Seeder memuat kata sandi demo (lihat bagian 6): catat daftar akun di luar repo.

## 3. Struktur

```
app/Models/            User, Field, Booking, Review
app/Http/Controllers/  Home (menyiapkan $hero), Field, Gallery{index}, Booking{store}, Payment{show,store}, History{index,batal},
                       Review{store},
                       Admin/Booking{index,bukti,setujui,tolak},
                       Admin/Field{index,create,store,edit,update,status},
                       Admin/Dashboard (invokable),
                       Admin/Report{index,csv; privat susun,rentang,tanggal,rupiah,barisCsv},
                       Admin/User{index}
app/Http/Requests/     StoreBookingRequest, SubmitProofRequest, RejectBookingRequest, FieldRequest, StoreReviewRequest
app/Policies/          BookingPolicy{viewAny,create,view,pay,cancel,review,kelola,verify}, FieldPolicy{kelola,ubah}, UserPolicy{kelola}
database/migrations/   ... create_reviews_table (2026_11_20_094942)
database/seeders/      DatabaseSeeder (admin + 10 pelanggan + 8 lapangan + booking demo + 42 ulasan demo; privat buatUser, buatBooking, buatUlasan, buatUlasanTambahan, buatAkunDemo, hitungUlangRating, ulasanTambahan, dataLapangan)
resources/views/       home, fields/*, galeri/index, payments/show, history/index, auth/{login,register},
                       admin/dashboard, admin/bookings/index, admin/fields/{index,form}, admin/reports/index, admin/users/index,
                       components/{field-card,ikon-fasilitas,ikon-keunggulan,admin-layout,admin-sisi,grafik-batang,auth-layout,application-logo}, layouts/*
public/css/            garuda, katalog, detail, galeri, ulasan, beranda, pembayaran, riwayat, auth, admin-booking, admin-lapangan, admin-layout, admin-dashboard, admin-laporan, admin-pengguna
config/garuda.php      jam operasional, batas bayar
tests/Feature/         Fondasi, Locale, Katalog, DetailLapangan, Booking, Payment, Riwayat, AdminBooking, AdminField, AdminLayout, NavbarAdmin, DashboardAdmin, ReportAdmin, UserAdmin, AuthTampilan, IkonFasilitas, Galeri, Review, RiwayatUlasan, DetailUlasan, Beranda, BerandaHero, AkunDemo (+ tes Breeze)
```

Rute publik: `/` (`home`), `/lapangan` (`lapangan.index`), `/lapangan/{field}` (`lapangan.show`; `?tanggal=`, `?ulasan=semua`), `/galeri` (`galeri.index`). Rute pelanggan (middleware `auth`): `/booking` (POST), `/pembayaran/{booking:code}` (GET/POST), `/riwayat`, `/riwayat/{booking:code}/batal` (POST), `/riwayat/{booking:code}/ulasan` (POST, `riwayat.ulasan`). Rute admin ada di grup `Route::prefix('admin')->name('admin.')` di `routes/web.php`: `/` (dashboard), `/booking`, `/booking/{booking:code}/{bukti|setujui|tolak}`, `/lapangan`, `/lapangan/tambah`, `/lapangan/{field}/{ubah|status}`, `/pengguna`, `/laporan`, `/laporan/csv`. Controller memakai alias `AdminXController` di `use`.

## 4. Skema (sudah dimigrasi)

`?` = boleh NULL.

| Tabel | Kolom penting |
| --- | --- |
| `users` | name, email, password, phone?, role (`admin`\|`pelanggan`, bawaan `pelanggan`) |
| `fields` | name (unik), type (`interlock`\|`rumput`\|`vinyl`), price_per_hour, location, description, size, capacity, facilities (JSON), image, rating, reviews_count, status (`aktif`\|`nonaktif`) |
| `bookings` | code (unik, `GA-ddmm-XXXX`), user_id, field_id, date, start_hour, duration, price_per_hour (salinan), total, status, expires_at?, proof_path?, proof_name?, proof_submitted_at?, rejection_reason?, cancelled_reason?, verified_by?, verified_at?; indeks (field_id, date) |
| `reviews` | user_id, field_id, booking_id (**unik**, satu ulasan per booking), rating (1–5), comment (5–500 karakter), timestamps; indeks (field_id, created_at); semua FK `cascadeOnDelete` |

Slot **tidak** punya tabel: ketersediaan dihitung dari `bookings` yang tidak `dibatalkan`. Laporan **tidak** butuh tabel baru: dihitung dari `bookings.date`.

## 5. Aturan bisnis dan peran

**Status:** `menunggu_pembayaran` → `menunggu_verifikasi` → `dikonfirmasi` → `selesai`; juga `dibatalkan`.

- **Cek bentrok oleh server** di `Booking::createPending()`: `DB::transaction` + `lockForUpdate` pada baris lapangan; tumpang tindih `start < end_lain` dan `start_lain < end`, status bukan `dibatalkan`; bertetangga boleh. Hanya lapangan `aktif` yang bisa dipesan. Total = harga per jam × durasi (harga disalin).
- **`Booking::perbaruiStatus()`** (tanpa scheduler; dipanggil sebelum membaca data, juga oleh dashboard, riwayat, detail lapangan, dan `ReviewController::store`): `menunggu_pembayaran` lewat `expires_at` → `dibatalkan` ("Waktu pembayaran habis"); `dikonfirmasi` yang jam selesainya sudah lewat → `selesai` (dihitung di PHP agar sama di SQLite/MariaDB). **Usul belum dibuat:** `menunggu_verifikasi` yang jam mainnya lewat → `dibatalkan`.
- **`kirimBukti()`:** cek ulang `bisaDibayar()` di transaksi + `lockForUpdate`, simpan ke disk `local`, status → `menunggu_verifikasi`, `expires_at` NULL, `rejection_reason` NULL.
- **`batalkan()`:** cek ulang di transaksi + `lockForUpdate`; hanya `menunggu_pembayaran`; selain itu `ValidationException` kunci `booking`. Alasan "Dibatalkan pelanggan".
- **`setujui(User $admin)`:** hanya dari `menunggu_verifikasi` (cek ulang di transaksi + kunci baris, selain itu `ValidationException` kunci `booking`) → `dikonfirmasi`, isi `verified_by`/`verified_at`.
- **`tolak(User $admin, string $alasan)`:** hanya dari `menunggu_verifikasi`. Jam mulai belum lewat → `menunggu_pembayaran` dengan `expires_at` baru; sudah lewat → `dibatalkan` (`cancelled_reason` "Bukti pembayaran ditolak: ..."). `rejection_reason` selalu terisi; berkas bukti dihapus dan `proof_*` dikosongkan; `verified_by`/`verified_at` diisi.
- **Ulasan (H3, selesai):** `POST /riwayat/{kode}/ulasan` → `StoreReviewRequest` (`authorize()` lewat `BookingPolicy::review`: bukan pemilik atau admin = 404; rating 1–5, komentar 5–500) → `Review::buat(User, Booking, rating, komentar)` di `DB::transaction` + `lockForUpdate` pada booking dan lapangan; cek ulang pemilik dan `bisaDiulas()` (selain itu `ValidationException` kunci `booking`). **Rating lapangan = rata-rata berbobot:** `(rating × reviews_count + bintang) ÷ (reviews_count + 1)`, dibulatkan 1 desimal, `reviews_count` +1. **Syarat `bisaDiulas()`: status `dikonfirmasi` atau `selesai`, dan belum punya ulasan.** Status booking tidak berubah oleh ulasan.

| Kemampuan | Tamu | Pelanggan | Admin |
| --- | :-: | :-: | :-: |
| Lihat beranda, daftar, detail lapangan (+ ulasan), galeri | ya | ya | ya |
| Buat booking, unggah bukti, lihat riwayat sendiri | – | ya | – |
| Batalkan booking sendiri (hanya `menunggu_pembayaran`) | – | ya | – |
| Tulis ulasan + rating untuk booking miliknya yang `dikonfirmasi`/`selesai`, sekali per booking | – | ya | – |
| Verifikasi booking (setujui/tolak/unduh bukti), lihat semua booking | – | – | ya |
| Kelola lapangan (tambah, ubah, aktif/nonaktif) | – | – | ya |
| Dashboard, laporan + CSV, daftar pengguna (hanya lihat) | – | – | ya |

Pelanggan **hanya** melihat booking miliknya (orang lain = 404). Admin dibuat lewat seeder. **Verifikasi email dimatikan.**

## 6. Keputusan aktif (ringkas)

- Tanpa payment gateway (bukti transfer + verifikasi admin), fasilitas = JSON, tanpa verifikasi email. Elemen palsu prototipe tidak dibawa. Ulasan + rating sungguhan dibuat di H3 (permintaan aslab).
- `/dashboard` Breeze dihapus; setelah login semua peran ke `home`. Filter/urutan/ketersediaan di **server** lewat parameter URL, tanpa JavaScript. Detail lapangan `nonaktif` = 404.
- Buat booking: `StoreBookingRequest::authorize()` lewat `BookingPolicy::create` (admin 403). Tanggal form dari `?tanggal=` (tak valid/lewat → hari ini); jam lewat radio, durasi 1–3. Setelah sukses redirect ke `pembayaran.show`. **Detail lapangan: kartu booking di kolom kanan ikut ter-scroll (tanpa `position: sticky`; sudah dicek pemilik).**
- Bukti bayar hanya **JPG/PNG, maks. 2 MB**. Orang lain termasuk admin = 404 di `pembayaran.show/store`. Nomor rekening fiktif (1234 5678 9012) di view `payments/show`. Sisa waktu bayar = menit dari server. Pada tahap `bayar`, bila `rejection_reason` terisi, tampil kotak `.pembayaran-galat`.
- `/riwayat`: admin **403**, tamu ke login. Gagal batal lewat `@error('booking')`; sukses lewat flash `riwayat_pesan`.
- **Panel admin booking:** `/admin/booking`; pelanggan **403**. Filter `?status=` dan `?q=`; tab status menampilkan jumlah. Tolak memakai kolom alasan per baris (5–255 karakter). Flash `admin_pesan`; galat `@error('booking')`, `@error('alasan')`. Unduh bukti yang berkasnya tidak ada → kembali ke daftar dengan galat `booking`.
- **Kelola lapangan:** `/admin/lapangan`; menonaktifkan lapangan tidak membatalkan booking yang ada; tidak ada hapus lapangan. Nama unik; tarif Rp10.000–Rp5.000.000; foto JPG/PNG maks. 2 MB, tanpa foto = `images/2-venue-interlock.jpg`; fasilitas = teks berkoma (maks. 12 butir @ 40 karakter); rating dan ulasan baru = 0.
- **Navigasi publik:** pelanggan melihat "Riwayat Booking"; admin satu tombol oranye `.tombol-panel-admin` "Panel Admin" menuju `admin.dashboard` (`$urlPanel` di `@php` atas `navigation.blade.php`).
- **Dashboard:** `/admin` (invokable). 4 kartu, grafik 7 hari (`x-grafik-batang`), 5 booking terbaru. Redirect admin ke dashboard setelah login belum dibuat (opsional).
- **Laporan:** `/admin/laporan` + `/csv`; tab Minggu/Bulan/Tahun/Kustom lewat `GET`; hanya booking `dikonfirmasi` dan `selesai`; 8 kartu, perbandingan periode, tabel per lapangan. CSV: `;`, BOM UTF-8. Detail: baca `ReportController::susun()`.
- **Kelola pengguna:** `/admin/pengguna`; pencarian `?q=`; tanpa hapus/ubah peran.
- **Login/daftar (H0), ikon + animasi fasilitas (H1), galeri (H2), ulasan (H3, H3b), hero beranda (H4 sebagian):** kode selesai, **belum dicek browser** (kecuali yang tercatat di bagian 7). Galat validasi bawaan Laravel masih **bahasa Inggris** (belum ada `lang/id`; opsional); galat ulasan sudah berbahasa Indonesia.
- **Ulasan — tampilan:** form di `/riwayat` (panel di bawah kartu booking, dibungkus `.riwayat-butir`): bintang = radio + CSS (urutan HTML 5..1, `flex-direction: row-reverse`, tanpa JS) + textarea; input tersembunyi `kode` dicocokkan dengan `old('kode')`; ulasan terkirim tampil sebagai panel "Ulasan kamu" tanpa form. **Detail lapangan:** kartu "Ulasan Pelanggan (N)" menampilkan **5** ulasan terbaru (`FieldController::ULASAN_AWAL`); bila > 5 muncul "Lihat selengkapnya (N ulasan)" → `?ulasan=semua#ulasan` dan "Tampilkan lebih sedikit"; nilai `ulasan` selain `semua` diabaikan; kosong → "Belum ada ulasan untuk lapangan ini." Komentar di-escape Blade. `HistoryController` memuat `with(['field', 'review'])`.
- **Akun demo (seeder, 22 Nov):** kata sandi ada di seeder (ikut ke GitHub; jangan deploy publik; **jangan tampil di halaman login**, daftar akun disimpan pemilik di luar repo). Admin: `garuda@gmail.com`. Pelanggan lama (6, kata sandi `DEMO_PASSWORD`): Andi, Sari, Budi, Rina, Dewi, Fajar (`@garudaarena.test`). **Pelanggan baru (4, nama pendek, `@gmail.com`, kata sandi `<nama>123`):** Azkal, Furqan, Fandi, Fadel, dibuat `buatAkunDemo()`; masing-masing 4–5 booking (2 `selesai` + diulas, `dikonfirmasi`, `menunggu_verifikasi`/`menunggu_pembayaran`; Fadel juga 1 `dibatalkan`) pada tanggal -3..-1 dan +4..+5 agar tidak bentrok. Total 10 pelanggan.
- **Ulasan — seeder (H3b):** 42 ulasan: tiga awal + 31 tambahan dari `ulasanTambahan()` (booking `selesai` sendiri, kode `U001`..`U031`, pengulas bergilir dari 6 pelanggan lama) + 8 dari akun baru. `hitungUlangRating()` di akhir `run()` menghitung `rating` dan `reviews_count` dari tabel `reviews`: VIP 8, Merdeka 5, Champions 7, Rencong 5, Seulawah 5, Lampuuk 2 (nonaktif), Peunayong 5, Krueng 5. **Booking Sari `L2M2` (VIP, selesai) sengaja belum diulas** untuk demo (VIP jadi 9); `A1B1` (Andi) dan `C3D3` (Budi) `dikonfirmasi`, juga bisa diulas; booking `dikonfirmasi` akun baru juga bisa diulas. Booking `selesai` ikut masuk dashboard/laporan admin.
- **Beranda (H4, sebagian):** **terkonfirmasi:** `HomeController` menyiapkan `$hero`; `home.blade.php` memuat `.hero-lencana-rating`; `x-ikon-keunggulan`; `beranda.css`; `BerandaHeroTest`; `BerandaTest` memeriksa "Cek Ketersediaan Cepat", "Lihat Selengkapnya", `logo-kotak`, "FUTSAL & SPORT" (lulus, jadi kemungkinan besar sudah ada; tampilan belum dicek browser). **Belum dikerjakan (opsional):** filter jenis di beranda, deskripsi + ikon lokasi di kartu, tombol oranye "Pesan Lapangan".
- **Panel admin vs prototipe (sisa opsional):** topbar prototipe punya kalimat deskripsi dan avatar "AD"; kartu statistik/filter jenis/kolom rating di Kelola Lapangan; ikon di kotak cari.
- **Catatan data demo:** booking demo `menunggu_verifikasi` punya `proof_name` tetapi `proof_path` kosong (ditangani). Booking `menunggu_pembayaran` (lama dan akun baru) kedaluwarsa 1 jam setelah seeder; jalankan `migrate:fresh --seed` tepat sebelum demo. Foto lapangan demo memakai `images/...`; foto unggahan butuh `storage:link`. `migrate:fresh --seed` menghapus booking dan akun buatan pemilik.

## 7. Pengujian

**211 passed** (763 assertions; hasil pemilik, 22 Nov; ganti bila beda). `BookingTest`, `PaymentTest`, `RiwayatTest`, `AdminBookingTest`, `DashboardAdminTest`, `ReviewTest`, `RiwayatUlasanTest`, `DetailUlasanTest` membekukan waktu di 20 Nov 2026 10:00. Tes tidak memeriksa CSS. `AdminFieldTest` memakai `Storage::fake`. `GaleriTest` (6), `AuthTampilanTest` (3), `IkonFasilitasTest`, `BerandaTest`, `BerandaHeroTest` ada (baca isinya sebelum mengubah komponen/beranda). **Ulasan:** `ReviewTest` (9), `RiwayatUlasanTest` (5), `DetailUlasanTest` (7; lapangan uji dihapus dulu karena senama lapangan seeder; angka VIP 8 dan Merdeka 5 tetap benar). **`AkunDemoTest` (4):** admin baru + sandi, 4 pelanggan demo (sandi, ≥4 booking, 2 ulasan sendiri), login `azkal@`, booking seeder tidak bentrok (ada `assertGreaterThan(40, ...)` di luar loop). `FondasiTest` seeder: 8 lapangan (7 aktif), 1 admin, **10 pelanggan**. Tes Breeze tidak memeriksa teks halaman.

**Dikonfirmasi pemilik di browser:** `/admin/booking` layar lebar (17 Nov); logo bola sidebar admin (19 Nov); halaman Pengguna layar lebar (19 Nov); detail lapangan dengan "Ulasan Pelanggan" (20 Nov, DB lama); `/riwayat` (20 Nov, DB lama); beranda (22 Nov, DB lama, sebelum hero H4); detail lapangan: kartu booking ikut ter-scroll (22 Nov).

**Belum dilakukan:**
- **Browser:** setelah `php artisan migrate:fresh --seed`: login `garuda@gmail.com` dan tiap akun baru (riwayat, ulasan sendiri); beranda baru (hero, lencana rating, ikon keunggulan, layar lebar dan sempit); detail VIP "(8)" + 5 ulasan + "Lihat selengkapnya" → 8 ulasan; detail Merdeka 5 ulasan tanpa tombol; angka ulasan beranda = detail; form bintang di `/riwayat` (hover, terpilih, fokus keyboard, layar sempit, galat); kirim ulasan sebagai `sari@` (VIP 8 → 9); dashboard/laporan dengan booking `selesai` tambahan. Juga `/login` + `/register`, logo bola navbar publik, ikon sidebar solid, ikon/animasi fasilitas, `/galeri`, `/admin`, `/admin/laporan` + CSV, `/admin/lapangan` + form, layar sempit panel admin, pembayaran (semua tahap). Selalu Ctrl+Shift+R.
- Unggah bukti bayar dan foto lapangan sungguhan; alur per peran.
- Balapan dua klik hanya terbukti lewat penalaran (`lockForUpdate` tidak berefek di SQLite).

## 8. Langkah berikutnya (satu langkah per percakapan)

**Selesai:** Langkah 0, A, B1, B2, C, D1, CSS (R1–R3b), D2, E1, E1b, E2, F-naskah, G1, G1b, G2, G3 + G3b, G4, H0, H1, H2, H3, H3b, H4 sebagian, **akun demo presentasi** (admin `garuda@gmail.com` + 4 pelanggan baru, 211 passed). Pastikan `git status` bersih (commit akun demo) sebelum mulai.

| Langkah | Isi | Target |
| --- | --- | --- |
| **F2** | Gladi demo: `migrate:fresh --seed`, telusur browser per peran (admin `garuda@`, pelanggan akun baru dan lama; termasuk H0–H4), perbaikan temuan, naskah demo diperbarui (sidebar, dashboard, laporan, pengguna, galeri, ulasan, beranda, akun demo) | 22–23 Nov (demo 23 Nov) |
| **H4 (sisa, opsional)** | Hanya bila ada waktu: filter jenis di beranda, deskripsi + ikon lokasi kartu, tombol "Pesan Lapangan". Cek `grep` dulu | 22 Nov |

**Bila waktu mepet, korbankan fitur tambahan, bukan F2.**

Sisa/opsional (bisa dibahas bersama; struktur auth belum dilihat, minta `routes/auth.php` dan `AuthenticatedSessionController.php`): redirect admin ke dashboard setelah login, pesan validasi `lang/id`, deskripsi di topbar admin, kartu statistik di Kelola Lapangan, README akar, Pull Request ke `main`, `menunggu_verifikasi` lewat jam → `dibatalkan`, nomor rekening ke `config/garuda.php`.
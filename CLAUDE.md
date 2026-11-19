# CLAUDE.md — Garuda Arena (Laravel)

> Serah-terima antar sesi Claude (versi ringkas). Riwayat langkah selesai ada di `RIWAYAT.md` (minta bagian tertentu bila perlu).
> Terakhir diperbarui: **19 Nov 2026** · Tes: **165 passed** (ganti bila hasil pemilik berbeda) · Laravel 13 + Breeze (Blade).
> **Target:** demo presentasi UAS (**23 atau 26 Nov 2026**; rencanakan 23 Nov).

## 0. Cara kerja dan aturan

Pemilik **bukan programmer mahir** (vibecoder): jelaskan perintah dengan jelas, beri satu kalimat "apa yang diubah dan kenapa" di tiap serahan kode.

**Cara serah kode:** Claude menulis kode **di chat, per file, lengkap**; pemilik menyalin lewat editor. Claude tidak melihat repo dan tidak bisa menjalankan tes; ia hanya tahu yang ditempel pemilik.

1. **Mulai dengan `git status --short` dan `ls`.** Kode di repo lebih benar daripada berkas ini. Bila ada berkas `??`/` M` yang tampak pekerjaan langkah ini, minta isinya dulu sebelum menulis ulang (G2 pernah sudah selesai tanpa tercatat; perubahan G3b juga sempat tidak tercatat).
2. **Jangan mengarang isi file yang belum dilihat.** Minta tempel dulu. Berkas bersama (`routes/web.php`, Policy, layout, model, `garuda.css`, `navigation.blade.php`) jangan ditimpa utuh sebelum dilihat versi terbarunya (Pint mengubah kode yang tersimpan).
3. **Satu langkah per percakapan.** Untuk tes cukup baris `Tests: N passed` kecuali ada yang gagal (`php artisan test 2>&1 | tail -n 8`). File panjang: `grep -n "function"` dulu. "Cari-ganti" hanya bila teks lama ditampilkan lengkap, unik, dan satu blok jelas; selain itu file utuh. Perintah `git diff` selalu pakai `git --no-pager`.
4. **Serahan memuat:** (a) file BARU/UBAH, (b) perintah untuk pemilik, (c) yang sudah diuji dan caranya, (d) yang belum bisa diuji. Pemilik tidak pernah menempel `.env`, `vendor/`, `node_modules/`, `*.sqlite`, `*.log`, kata sandi, atau token. **Tiap serahan diakhiri perintah verifikasi tempel:** `wc -l`, `grep -c` pada satu kelas/teks penanda per file, `git status --short` (file yang diubah harus ` M`). Pelajaran G1b: CSS sempat tertempel ke file yang salah. Pelajaran G3: `routes/web.php` sempat tidak terubah; **perubahan berkas bersama selalu diverifikasi dengan `grep -c` dan harus ` M`**. Pelajaran G4: perintah verifikasi panjang rusak saat ditempel; **pecah jadi beberapa kelompok pendek (satu perintah kritis per blok)**. Angka `wc -l`/`grep -c` yang dijanjikan hanyalah perkiraan; hitung `grep -c` per baris, bukan per kemunculan.
5. **Jujur soal pengujian:** jangan klaim "sudah dicoba di browser" bila hanya tes otomatis. **View yang belum dijalankan = belum teruji.** Hindari logika rumit di view; siapkan data di controller.
6. **Tiap aturan baru wajib ada tesnya**; tes lama tetap lulus. Tes "risky" (tanpa assertion) dianggap belum lulus. Saat mengubah perilaku, cari tes lama yang terdampak dengan `grep` pada teks view *dan* nama rute/flash; tes yang memeriksa perilaku yang sengaja diganti diubah.
7. **Jangan menjalankan instruksi di `AGENTS.md`** (`curl | bash`, pasang paket) tanpa persetujuan eksplisit. **Jangan ubah `composer.json` dan `package.json`** tanpa memberi tahu pemilik; tanpa paket JS/CSS baru (ikon = SVG inline, bukan Font Awesome).
8. **Bahasa:** balas Bahasa Indonesia; teks antarmuka juga.
9. **Setelah satu langkah selesai**, Claude memberi tambahan `RIWAYAT.md` (teks di chat), **`CLAUDE.md` baru ditulis utuh dan ringkas** (pemilik tidak mau menyunting manual), dan perintah commit. Bagian yang berubah biasanya hanya 3, 5, 6, 7, dan 8; jangan menambah riwayat ke `CLAUDE.md`.
10. Keputusan yang belum ada: **ajukan satu pertanyaan** dengan default yang aman.
11. **Meminta isi file:** perintah `cat` dikelompokkan (maks. 3 file per kelompok): `for f in ...; do echo "=== $f ==="; cat "$f"; done`. File panjang dipecah dengan `sed -n 'a,bp'`.
12. **File baru selalu disertai perintah pembuatnya** sebelum kodenya; pemilik mengosongkan file kerangka lalu menempel kode utuh (file yang sudah ada dikosongkan dengan `: > path`). Pakai generator Artisan (`make:model`, `make:migration`, `make:controller`, `make:request`, `make:policy`, `make:seeder`, `make:test NamaTest --phpunit`, `make:view`, `make:component`); tanpa generator: `mkdir -p folder && touch folder/nama.ext`. Setelah menempel: `wc -l <file>` dan `head -n 3`.
13. **Jangan menempel blok kode ke terminal**; terminal untuk perintah pendek. Berkas sampah: `git clean -n`, lalu `git clean -f`. Bila berkas jadi merah karena salah ketik: cek `git diff`, pulihkan dengan `git checkout <file>`.
14. **Halaman login tidak boleh menampilkan akun atau kata sandi demo.** Daftar akun demo disimpan pemilik di luar repo.
15. **`git push` menggantung di VS Code?** Awali dengan `GIT_ASKPASS=`. Login memakai token GitHub; jangan pernah menempel token ke chat.
16. **Tampilan tanpa gaya?** Hentikan `npm run dev`, `php artisan optimize:clear`, `npm run dev`, Ctrl+Shift+R.
17. **CSS (aturan dosen, nilai dikurangi bila dilanggar):** CSS terpisah di `public/css/`. Di view buatan sendiri dilarang `<style>`, `style=""`, dan kelas Tailwind; pakai kelas buatan sendiri (bahasa Indonesia, istilah umum tetap Inggris: `navbar`, `footer`, `hero`, `grid`). CSS polos tanpa `@apply`, responsif lewat `@media (min-width: ...)`. Variabel di `:root` pada `garuda.css` (`--hijau` #15803d, `--hijau-800`, `--hijau-tua` #14532d, `--hijau-muda`, `--hijau-100`, `--hijau-200`, `--aksen` #ea580c, `--aksen-tua`, `--aksen-muda`, `--latar`, `--garis`, `--abu-100/500/600/800`, `--teks`, `--font-judul` Poppins, `--font-isi` Inter). Berkas per halaman dimuat lewat `<x-slot name="head"><link rel="stylesheet" href="{{ asset('css/nama.css') }}"></x-slot>`. Setelah menempel CSS: `wc -l`, `head -n 3`, jumlah `{` = `}`. Cek bersih view: `grep -nE 'class="[^"]*(bg-|text-|px-|py-|rounded|border-gray|flex|grid-cols|space-)|style=|<style' <view>` harus kosong. Tiap serahan view memuat berkas CSS-nya dan daftar cek browser (layar lebar dan sempit).
18. **Tailwind tetap dimuat lewat `@vite`**, jangan dicabut: halaman Breeze (lupa/reset/konfirmasi kata sandi, profil) dan komponen `dropdown`, `dropdown-link`, `modal` bergantung padanya. `garuda.css` dimuat setelah `@vite`. Tampil/sembunyi pakai Alpine `x-show` + `x-cloak`, bukan kelas `hidden`.
19. **Grafik = SVG dihitung di server** (komponen Blade, mis. `x-grafik-batang`), tanpa paket grafik JS. Atribut SVG (`width`, `height`, `x`, `y`, `viewBox`, `fill`, `stroke`) boleh; `style=""` tetap dilarang (warna lewat kelas CSS).
20. **Animasi (H1):** hanya CSS (transisi, `@keyframes`), tanpa paket dan tanpa JS baru; wajib menghormati `@media (prefers-reduced-motion: reduce)`.

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

- Tabel/kolom/model **Inggris**; **URI dan teks UI Indonesia**. Status berupa teks Indonesia snake_case, konstanta model (`Booking::STATUS_*`, `User::ROLE_*`, `Field::STATUS_*`, `Field::TYPE_*`).
- **Satu controller per modul**; logika multi-tabel di `DB::transaction`, idealnya metode model. Validasi di FormRequest, otorisasi di Policy (`Gate::authorize`, `authorize()` FormRequest, `@can`). Booking milik orang lain = `Response::denyAsNotFound()` (404). Aksi khusus admin = 403 untuk non-admin.
- **Peran:** `role` sengaja **tidak** di `#[Fillable]` `User`; registrasi publik selalu `pelanggan`. Ubah peran lewat `forceFill` (tes membuat admin dengan `User::factory()->create()` lalu `forceFill(['role' => User::ROLE_ADMIN])->save()`).
- **Uang** = bilangan bulat rupiah. Waktu booking: `date`, `start_hour` (8–22), `duration` (1–3 jam); jam selesai = `start_hour + duration` ≤ 23 (`Booking::endHour()`). Konfigurasi di `config/garuda.php` (`open_hour=8`, `close_hour=23`, `pay_window_minutes=60`).
- **Tanggal dan jam hanya lewat Carbon.** Nama bulan di view: `->locale('id')->translatedFormat(...)`. Tes membekukan waktu dengan `Carbon::setTestNow(...)`.
- **Format tampilan lewat metode model** (`Field::priceLabel()`, `ratingLabel()`, `typeLabel()`, `imageUrl()`, `isActive()`, `statusLabel()`, `fasilitasTeks()`, `gambarUnggahan()`; `Booking::jamLabel()`, `totalLabel()`, `tanggalLabel()`, `tarifLabel()`, `sisaMenit()`, `bisaDibayar()`, `bisaDiverifikasi()`, `statusLabel()`, `statusKelas()`, `Booking::daftarStatus()`; `User::peranLabel()`, `bergabungLabel()`), bukan logika di view. Filter/urutan/pencarian lewat parameter URL yang divalidasi di controller (nilai tak dikenal diabaikan).
- **Bukti bayar** di disk privat (`local`, `bukti-bayar/`), tidak pernah `public`; tipe dan ukuran divalidasi di server; unduh lewat rute yang dicek hak. **Foto lapangan** di disk `public` (`storage/lapangan/`), dicatat sebagai `storage/...`; gambar bawaan `images/...` tidak pernah dihapus.
- **Layout publik** `x-app-layout` memuat `layouts.navigation` dan `layouts.footer`, slot `header` dan `head` opsional. **Layout admin** `x-admin-layout` (komponen `x-admin-sisi` untuk isi sidebar): sidebar kiri hijau tua (tersembunyi di bawah 768px, diganti menu geser Alpine `buka`), topbar (judul "Panel Admin" + avatar inisial `.admin-avatar` + nama), slot `head`/`header` opsional; tanpa navbar dan footer publik. Menu sidebar dari daftar `[label, rute, pola, ikon]` di bagian `@php` atas `admin-sisi.blade.php`, hanya tampil bila `Route::has(rute)`; urutan **Dashboard, Kelola Lapangan, Kelola Booking, Kelola User, Laporan** (rute `admin.dashboard`, `admin.fields.index`, `admin.bookings.index`, `admin.users.index`, `admin.reports.index`). **Ikon sidebar solid** (SVG inline `fill="currentColor"`, tanpa Font Awesome); ikon bola futsal didefinisikan sekali sebagai `$bola` dan dipakai untuk logo dan menu Lapangan.
- **Layout auth** `x-auth-layout` (prop `judul`) khusus `auth/login` dan `auth/register`: kartu dua kolom (panel hijau kiri + formulir kanan), memuat `@vite`, `garuda.css`, `auth.css`. `layouts/guest.blade.php` **tidak diubah** dan tetap dipakai halaman lupa/reset/konfirmasi kata sandi (tampilan Breeze). Tanpa tombol sosial (Google/Facebook/VK) karena palsu.
- **Logo:** komponen `x-application-logo` = bola futsal SVG (garis luar `stroke="currentColor"`); dipakai `navigation.blade.php` (kelas `.logo-navbar`, `fill` + `color: var(--hijau)`), `guest.blade.php`, dan `auth-layout`.
- **Pembagian CSS:** `admin-layout.css` = kerangka (sidebar, menu geser, topbar, avatar; dimuat oleh layout). `admin-booking.css` = `.admin-cari*`, `.admin-tabel*` (termasuk `.admin-tabel-bungkus`), `.admin-kode`, `.admin-kecil`, `.admin-kosong`, `.admin-aksi`, `.admin-tombol-{bukti,setuju,tolak}`, `.admin-form-tolak`. `admin-lapangan.css` = `.admin-tombol-tambah`, `.lencana-lapangan`, `.form-*`, `.tombol-simpan`. `admin-dashboard.css` = `.dashboard-*` dan `.grafik-*` (SVG `x-grafik-batang`). `admin-laporan.css` = `.laporan-*`. `admin-pengguna.css` = `.lencana-peran*`. `riwayat.css` = `.riwayat-*`, `.filter-status`, `.filter-tombol`, `.lencana-status`, `.status-*`. `pembayaran.css` = `.tombol-kirim`, `.tombol-kembali`, `.pembayaran-galat`. **`auth.css` = `.auth-*`** (halaman, kartu, visual, logo, formulir, label, input + `.galat`, galat-teks, info, baris, ingat, lupa, tombol, pindah). `garuda.css` = variabel, navbar, footer, `.tombol-panel-admin` (akhir berkas). Halaman admin lain memuat ulang berkas yang dibutuhkan lewat slot `head`; **halaman admin baru memuat `riwayat.css` + `admin-booking.css`** (+ `admin-dashboard.css` bila ada grafik).
- Jangan simpan rahasia di repo atau percakapan. Seeder memuat kata sandi demo sementara (`DEMO_PASSWORD`): ganti sendiri, catat di luar repo.

## 3. Struktur

```
app/Models/            User, Field, Booking                                          (ADA)
app/Http/Controllers/  Home, Field, Booking{store}, Payment{show,store}, History{index,batal},
                       Admin/Booking{index,bukti,setujui,tolak},
                       Admin/Field{index,create,store,edit,update,status},
                       Admin/Dashboard (invokable),
                       Admin/Report{index,csv; privat susun,rentang,tanggal,rupiah,barisCsv},
                       Admin/User{index}                                             (ADA)
app/Http/Requests/     StoreBookingRequest, SubmitProofRequest, RejectBookingRequest, FieldRequest (ADA)
app/Policies/          BookingPolicy{viewAny,create,view,pay,cancel,kelola,verify}, FieldPolicy{kelola,ubah}, UserPolicy{kelola} (ADA)
database/seeders/      DatabaseSeeder (admin + 3 pelanggan + 8 lapangan + 6 booking demo)
resources/views/       home, fields/*, payments/show, history/index, auth/{login,register},
                       admin/dashboard, admin/bookings/index, admin/fields/{index,form}, admin/reports/index, admin/users/index,
                       components/{field-card,admin-layout,admin-sisi,grafik-batang,auth-layout,application-logo}, layouts/*   (ADA)
public/css/            garuda, katalog, detail, pembayaran, riwayat, auth, admin-booking, admin-lapangan, admin-layout, admin-dashboard, admin-laporan, admin-pengguna (ADA)
config/garuda.php      jam operasional, batas bayar
tests/Feature/         Fondasi, Locale, Katalog, DetailLapangan, Booking, Payment, Riwayat, AdminBooking, AdminField, AdminLayout, NavbarAdmin, DashboardAdmin, ReportAdmin, UserAdmin, AuthTampilan (+ tes Breeze)

AKAN DIBUAT: components/ikon-fasilitas (H1), animasi di garuda.css/katalog.css (H1),
             Galeri{index} + view galeri + css/galeri.css + tes (H2),
             Review model/migrasi/Policy/FormRequest/controller + form di riwayat + daftar di detail + seeder + tes (H3)
```

Rute admin ada di grup `Route::prefix('admin')->name('admin.')` di `routes/web.php` (di dalam middleware `auth`): `/` (dashboard), `/booking`, `/booking/{booking:code}/{bukti|setujui|tolak}`, `/lapangan`, `/lapangan/tambah`, `/lapangan/{field}/{ubah|status}`, `/pengguna` (`users.index`), `/laporan` (`reports.index`), `/laporan/csv` (`reports.csv`). Controller memakai alias `AdminXController` di `use`.

## 4. Skema (sudah dimigrasi)

`?` = boleh NULL.

| Tabel | Kolom penting |
| --- | --- |
| `users` | name, email, password, phone?, role (`admin`\|`pelanggan`, bawaan `pelanggan`) |
| `fields` | name (unik), type (`interlock`\|`rumput`\|`vinyl`), price_per_hour, location, description, size, capacity, facilities (JSON), image, rating, reviews_count, status (`aktif`\|`nonaktif`) |
| `bookings` | code (unik, `GA-ddmm-XXXX`), user_id, field_id, date, start_hour, duration, price_per_hour (salinan), total, status, expires_at?, proof_path?, proof_name?, proof_submitted_at?, rejection_reason?, cancelled_reason?, verified_by?, verified_at?; indeks (field_id, date) |

**Rencana H3 (belum dimigrasi):** tabel `reviews`: user_id, field_id, booking_id (**unik**, satu ulasan per booking), rating (1–5), comment, timestamps.

Slot **tidak** punya tabel: ketersediaan dihitung dari `bookings` yang tidak `dibatalkan`. Laporan **tidak** butuh tabel baru: dihitung dari `bookings.date`.

## 5. Aturan bisnis dan peran

**Status:** `menunggu_pembayaran` → `menunggu_verifikasi` → `dikonfirmasi` → `selesai`; juga `dibatalkan`.

- **Cek bentrok oleh server** di `Booking::createPending()`: `DB::transaction` + `lockForUpdate` pada baris lapangan; tumpang tindih `start < end_lain` dan `start_lain < end`, status bukan `dibatalkan`; bertetangga boleh. Hanya lapangan `aktif` yang bisa dipesan. Total = harga per jam × durasi (harga disalin).
- **`Booking::perbaruiStatus()`** (tanpa scheduler; dipanggil sebelum membaca data, juga oleh dashboard): `menunggu_pembayaran` lewat `expires_at` → `dibatalkan` ("Waktu pembayaran habis"); `dikonfirmasi` yang jam selesainya sudah lewat → `selesai` (dihitung di PHP agar sama di SQLite/MariaDB). **Usul belum dibuat:** `menunggu_verifikasi` yang jam mainnya lewat → `dibatalkan`.
- **`kirimBukti()`:** cek ulang `bisaDibayar()` di transaksi + `lockForUpdate`, simpan ke disk `local`, status → `menunggu_verifikasi`, `expires_at` NULL, `rejection_reason` NULL.
- **`batalkan()`:** cek ulang di transaksi + `lockForUpdate`; hanya `menunggu_pembayaran`; selain itu `ValidationException` kunci `booking`. Alasan "Dibatalkan pelanggan".
- **`setujui(User $admin)`:** hanya dari `menunggu_verifikasi` (cek ulang di transaksi + kunci baris, selain itu `ValidationException` kunci `booking`) → `dikonfirmasi`, isi `verified_by`/`verified_at`.
- **`tolak(User $admin, string $alasan)`:** hanya dari `menunggu_verifikasi`. Jam mulai belum lewat → `menunggu_pembayaran` dengan `expires_at` baru (`pay_window_minutes`); sudah lewat → `dibatalkan` (`cancelled_reason` "Bukti pembayaran ditolak: ..."). `rejection_reason` selalu terisi; berkas bukti dihapus dan `proof_*` dikosongkan; `verified_by`/`verified_at` diisi.

| Kemampuan | Tamu | Pelanggan | Admin |
| --- | :-: | :-: | :-: |
| Lihat beranda, daftar, detail lapangan, **galeri (H2)** | ya | ya | ya |
| Buat booking, unggah bukti, lihat riwayat sendiri | – | ya | – |
| Batalkan booking sendiri (hanya `menunggu_pembayaran`) | – | ya | – |
| **Tulis ulasan + rating** untuk booking `selesai` miliknya, sekali per booking (H3) | – | ya | – |
| Verifikasi booking (setujui/tolak/unduh bukti), lihat semua booking | – | – | ya |
| Kelola lapangan (tambah, ubah, aktif/nonaktif) | – | – | ya |
| Dashboard, laporan + CSV, daftar pengguna (hanya lihat) | – | – | ya |

Pelanggan **hanya** melihat booking miliknya (orang lain = 404). Admin dibuat lewat seeder. **Verifikasi email dimatikan.**

## 6. Keputusan aktif (ringkas)

- Tanpa payment gateway (bukti transfer + verifikasi admin), fasilitas = JSON, tanpa verifikasi email. Elemen palsu prototipe tidak dibawa (termasuk tombol login sosial). **Keputusan lama "tanpa ulasan sungguhan" DIBALIK (permintaan aslab): ulasan + rating dibuat di H3.**
- `/dashboard` Breeze dihapus; setelah login semua peran ke `home`. Filter/urutan/ketersediaan di **server** lewat parameter URL, tanpa JavaScript. Detail lapangan `nonaktif` = 404.
- Buat booking: `StoreBookingRequest::authorize()` lewat `BookingPolicy::create` (admin 403). Tanggal form dari `?tanggal=` (tak valid/lewat → hari ini); jam lewat radio, durasi 1–3. Setelah sukses redirect ke `pembayaran.show`.
- Bukti bayar hanya **JPG/PNG, maks. 2 MB**. Orang lain termasuk admin = 404 di `pembayaran.show/store`. Nomor rekening fiktif (1234 5678 9012) tertulis di view `payments/show`. Sisa waktu bayar = menit dari server. Pada tahap `bayar`, bila `rejection_reason` terisi, tampil kotak `.pembayaran-galat`.
- `/riwayat`: admin **403**, tamu ke login. Gagal batal lewat `@error('booking')`; sukses lewat flash `riwayat_pesan`.
- **Panel admin booking:** `/admin/booking`; pelanggan **403**. Filter `?status=` dan `?q=`; tab status menampilkan jumlah. Tolak memakai kolom alasan per baris (5–255 karakter). Flash `admin_pesan`; galat `@error('booking')`, `@error('alasan')`. Unduh bukti yang berkasnya tidak ada → kembali ke daftar dengan galat `booking` (booking demo seeder memang tanpa berkas).
- **Kelola lapangan:** `/admin/lapangan`; menonaktifkan lapangan tidak membatalkan booking yang ada; tidak ada hapus lapangan. Nama unik; tarif Rp10.000–Rp5.000.000; foto JPG/PNG maks. 2 MB, tanpa foto = `images/2-venue-interlock.jpg`; fasilitas = teks berkoma (maks. 12 butir @ 40 karakter); rating dan ulasan baru = 0.
- **Navigasi publik:** pelanggan melihat "Riwayat Booking"; admin satu tombol oranye `.tombol-panel-admin` "Panel Admin" menuju `admin.dashboard`. Variabel `$urlPanel` dihitung di `@php` atas `navigation.blade.php`. **Tautan "Galeri" ditambah di H2** (file ini berkas bersama: minta isinya dulu).
- **Dashboard:** `/admin` (`Admin/DashboardController` invokable). 4 kartu, grafik 7 hari (`x-grafik-batang`, kini hanya dashboard), 5 booking terbaru. Redirect admin ke dashboard setelah login belum dibuat (opsional).
- **Laporan:** `/admin/laporan` + `/csv`; tab Minggu/Bulan/Tahun/Kustom lewat `GET`; hanya booking `dikonfirmasi` dan `selesai`; 8 kartu, perbandingan periode, tabel per lapangan; grafik tren sengaja dibuang. CSV: `;`, BOM UTF-8. Detail: baca `ReportController::susun()`.
- **Kelola pengguna:** `/admin/pengguna`; pencarian `?q=`; kolom nama, email, telepon, peran, jumlah booking, bergabung; tanpa hapus/ubah peran. Cek bersih view `admin/users/index.blade.php` **terbukti kosong (19 Nov)**.
- **Login/daftar (H0 selesai, tes 3 hijau, belum dicek browser):** desain dari prototipe tanpa tombol sosial; form daftar tetap Nama, Email, Kata sandi, Konfirmasi (dibutuhkan backend). Galat validasi masih **bahasa Inggris** (belum ada `lang/id`; opsional).
- **Keputusan H1–H3 (disetujui sebagian, 19 Nov):**
  - **Ikon fasilitas (H1):** fasilitas teks bebas, ikon dipetakan lewat **kata kunci** (parkir, toilet, kantin, wifi, musala, dst.) di komponen `x-ikon-fasilitas`; tak dikenal = ikon centang. SVG inline.
  - **Animasi (H1):** CSS saja (hover kartu/tombol, muncul halus saat halaman dimuat, `prefers-reduced-motion`). Animasi muncul saat scroll (butuh JS) ditunda.
  - **Galeri (H2):** **foto saja + keterangan nama tempat** (nama lapangan dan lokasi), tanpa tabel baru; sumber foto lapangan `aktif` lewat `Field::imageUrl()`; filter jenis lantai `?jenis=`; klik foto = tampilan besar (Alpine `x-show` + `x-cloak`); tautan di navbar.
  - **Ulasan + rating (H3):** tabel `reviews` (lihat bagian 4); hanya pelanggan pemilik booking `selesai`; satu ulasan per booking; `rating` dan `reviews_count` lapangan dihitung ulang di `DB::transaction`; bintang = radio + CSS tanpa JS; form di `/riwayat`, daftar ulasan di detail lapangan; **seeder diperluas** (booking `selesai` + beberapa ulasan) agar bisa didemokan.
- **Panel admin vs prototipe (sisa opsional):** topbar prototipe punya kalimat deskripsi di kiri dan avatar "AD"; kartu statistik/filter jenis/kolom rating di Kelola Lapangan; ikon di kotak cari.
- **Catatan data demo:** booking demo `menunggu_verifikasi` punya `proof_name` tetapi `proof_path` kosong (ditangani). Booking demo `menunggu_pembayaran` kedaluwarsa 1 jam setelah seeder; jalankan `migrate:fresh --seed` sebelum demo. Email admin seeder masih `admin@g` (janggal; ganti jadi `admin@garudaarena.test` bila pemilik setuju; login ikut berubah). Foto lapangan demo memakai `images/...`; foto unggahan butuh `storage:link`.

## 7. Pengujian

**165 passed** (hasil pemilik, 19 Nov; ganti bila beda). `BookingTest`, `PaymentTest`, `RiwayatTest`, `AdminBookingTest`, `DashboardAdminTest` membekukan waktu di 20 Nov 2026 10:00. Tes tidak memeriksa CSS. `AdminFieldTest` memakai `Storage::fake`. `AdminLayoutTest` (3), `NavbarAdminTest` (2), `DashboardAdminTest` (6), `ReportAdminTest` (13), `UserAdminTest` (6). **`AuthTampilanTest` (3):** login memuat "Masuk ke akun", "Siap main?", `css/auth.css`, tautan daftar, tanpa akun demo; daftar memuat field `name` dan `password_confirmation`; view `auth/login`, `auth/register`, `components/auth-layout` bersih dari gaya terlarang (regex aturan 17). Tes Breeze tidak memeriksa teks halaman, jadi aman mengindonesiakan teks.

**Dikonfirmasi pemilik di browser:** `/admin/booking` baik di layar lebar (17 Nov); logo bola sidebar admin sesuai (19 Nov); halaman Pengguna tampil baik di layar lebar (19 Nov).

**Belum dilakukan (telusur F2):**
- **Browser:** `/login` + `/register` (lebar dan sempit, galat salah sandi, daftar lalu mendarat di beranda), logo bola di navbar publik dan halaman lupa kata sandi, ikon sidebar solid (termasuk menu geser), `/admin` (kartu, grafik), `/admin/laporan` + CSV (dibuka di Excel/LibreOffice), `/admin/lapangan` + form, layar sempit panel admin, beranda, `/lapangan`, detail, pembayaran (semua tahap), `/riwayat`. Selalu dengan Ctrl+Shift+R.
- Unggah bukti bayar dan unggah foto lapangan sungguhan; alur per peran.
- Balapan dua klik hanya terbukti lewat penalaran (`lockForUpdate` tidak berefek di SQLite).

## 8. Langkah berikutnya (satu langkah per percakapan)

**Selesai:** Langkah 0, A, B1, B2, C, D1, CSS (R1–R3b), D2, E1, E1b, E2, F-naskah, G1, G1b, G2, G3 + G3b, G4, **H0** (logo bola di sidebar dan `x-application-logo`, ikon sidebar solid + urutan menu prototipe, desain login/daftar `x-auth-layout` + `auth.css`; tes 165 passed; tampilan browser belum dikonfirmasi).

| Langkah | Isi | Target |
| --- | --- | --- |
| **H1** | Komponen `x-ikon-fasilitas` + ikon di kartu fasilitas (kartu lapangan/detail) + animasi CSS (aturan 20) + tes | 20 Nov |
| **H2** | Halaman Galeri (foto + nama tempat, filter jenis, tampilan besar) + tautan navbar + tes | 21 Nov |
| **H3** | Ulasan + rating (migrasi, model, Policy, FormRequest, controller, form di riwayat, daftar di detail, rating lapangan dihitung ulang, seeder diperluas, tes) | 22 Nov |
| **F2** | Gladi demo: telusur browser per peran, perbaikan temuan, naskah demo diperbarui (sidebar, dashboard, laporan, pengguna, galeri, ulasan) | 22 Nov |

**Bila waktu mepet, H3 yang dikorbankan** (bukan H1/H2/F2). Telusur browser langkah yang sudah jadi (H0) boleh digabung ke F2.

Sisa/opsional: ganti `admin@g`, pesan validasi `lang/id`, redirect admin ke dashboard setelah login, deskripsi di topbar admin, kartu statistik di Kelola Lapangan, README akar, Pull Request ke `main`, `menunggu_verifikasi` lewat jam → `dibatalkan`, nomor rekening ke `config/garuda.php`.

### Langkah H1 — berikutnya
**Yang perlu ditempel saat mulai:** `git status --short`, `ls`, `php artisan test 2>&1 | tail -n 8`, lalu `cat` untuk `resources/views/components/field-card.blade.php`, bagian fasilitas di `resources/views/fields/show.blade.php` (cari dengan `grep -n -i "fasilitas" resources/views/fields/*.blade.php resources/views/components/field-card.blade.php`), `public/css/katalog.css`, `public/css/detail.css`, dan metode `Field::fasilitasTeks()` (`grep -n -A8 "function fasilitas" app/Models/Field.php`); juga daftar fasilitas yang dipakai seeder (`grep -n -i "facilities" -A3 database/seeders/DatabaseSeeder.php`) agar kata kunci ikon sesuai data nyata. `garuda.css` baru diminta bila animasi perlu masuk ke sana.
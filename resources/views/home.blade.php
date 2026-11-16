<x-app-layout>
    <section class="hero">
        <div class="wadah hero-isi">
            <div class="hero-teks">
                <h1 class="hero-judul">
                    Booking Lapangan Futsal, <span class="sorot">Tanpa Ribet</span>
                </h1>
                <p class="hero-paragraf">
                    Pilih lapangan, tanggal, dan jam. Bayar dengan unggah bukti transfer, lalu admin memverifikasi pesananmu.
                </p>
                <div class="hero-aksi">
                    <a href="{{ route('lapangan.index') }}" class="tombol tombol-utama">Pilih Lapangan</a>
                    <a href="#features" class="tombol tombol-garis">Lihat Keunggulan</a>
                </div>
                <dl class="hero-statistik">
                    <div>
                        <dt class="statistik-label">Lapangan aktif</dt>
                        <dd class="statistik-nilai">{{ $jumlahAktif }}</dd>
                    </div>
                    <div>
                        <dt class="statistik-label">Jam operasional</dt>
                        <dd class="statistik-nilai">{{ $jamBuka }}–{{ $jamTutup }}</dd>
                    </div>
                    <div>
                        <dt class="statistik-label">Batas bayar</dt>
                        <dd class="statistik-nilai">{{ $batasBayar }} menit</dd>
                    </div>
                </dl>
            </div>

            <div class="hero-gambar">
                <img src="{{ asset('images/1-hero-gedung-malam.jpg') }}" alt="Gedung Garuda Arena di malam hari" class="hero-foto">
            </div>
        </div>
    </section>

    <section class="wadah seksi">
        <div class="seksi-kepala sebaris">
            <div>
                <span class="label-seksi">Pilihan Lapangan</span>
                <h2 class="judul-seksi">Lapangan Favorit</h2>
            </div>
            <a href="{{ route('lapangan.index') }}" class="link-semua">Lihat semua lapangan →</a>
        </div>

        <div class="grid-kartu">
            @foreach ($unggulan as $field)
                <x-field-card :field="$field" />
            @endforeach
        </div>
    </section>

    <section id="features" class="wadah seksi">
        <div class="seksi-kepala tengah">
            <span class="label-seksi">Kenapa Memilih Kami</span>
            <h2 class="judul-seksi">Keunggulan Garuda Arena</h2>
        </div>
        <div class="grid-keunggulan">
            @foreach ($fasilitas as $item)
                <div class="kartu-keunggulan">
                    <h3 class="keunggulan-judul">{{ $item['judul'] }}</h3>
                    <p class="keunggulan-teks">{{ $item['teks'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="gallery" class="wadah seksi">
        <div class="seksi-kepala">
            <span class="label-seksi">Galeri Arena</span>
            <h2 class="judul-seksi">Suasana di Garuda Arena</h2>
        </div>
        <div class="grid-galeri">
            @foreach ($galeri as $foto)
                <div class="galeri-item">
                    <img src="{{ asset($foto['file']) }}" alt="{{ $foto['alt'] }}" loading="lazy">
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/beranda.css') }}">
    </x-slot>

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
                @if ($hero)
                    <div class="hero-kartu">
                        <img src="{{ asset('images/1-hero-gedung-malam.jpg') }}" alt="Gedung Garuda Arena di malam hari" class="hero-foto">
                        <div class="hero-kartu-bawah">
                            <div>
                                <h3 class="hero-kartu-nama">{{ $hero->name }}</h3>
                                <p class="hero-kartu-info">
                                    <svg class="hero-kartu-pin" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 2a7 7 0 00-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 00-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z"/>
                                    </svg>
                                    {{ $hero->typeLabel() }} • {{ $hero->location }}
                                </p>
                            </div>
                            <span class="lencana-tersedia">Tersedia</span>
                        </div>
                    </div>

                    <div class="hero-lencana-rating">
                        <span class="hero-lencana-ikon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="hero-lencana-label">Rating Pengguna</span>
                            <span class="hero-lencana-nilai">{{ $hero->ratingLabel() }} / 5 ({{ $hero->reviews_count }} ulasan)</span>
                        </span>
                    </div>
                @else
                    <img src="{{ asset('images/1-hero-gedung-malam.jpg') }}" alt="Gedung Garuda Arena di malam hari" class="hero-foto">
                @endif
            </div>
        </div>
    </section>

    <section class="wadah cek-cepat">
        <div class="cek-kartu">
            <h2 class="cek-judul">
                <svg class="cek-ikon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M13 2L4 14h6l-1 8 9-12h-6z"/>
                </svg>
                Cek Ketersediaan Cepat
            </h2>
            <form method="GET" action="{{ route('home') }}" class="cek-form">
                <div>
                    <label for="cek-lapangan" class="cek-label">Pilih Lapangan</label>
                    <select id="cek-lapangan" name="lapangan" class="cek-input">
                        <option value="semua">Semua Lapangan</option>
                        @foreach ($daftarLapangan as $lapangan)
                            <option value="{{ $lapangan->id }}">{{ $lapangan->name }} ({{ $lapangan->typeLabel() }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cek-tanggal" class="cek-label">Tanggal Main</label>
                    <input type="date" id="cek-tanggal" name="tanggal" value="{{ $hariIni }}" min="{{ $hariIni }}" class="cek-input" required>
                </div>
                <div class="cek-tombol-bungkus">
                    <button type="submit" class="cek-tombol">Cek &amp; Booking</button>
                </div>
            </form>
        </div>
    </section>

    <section class="wadah seksi">
        <div class="seksi-kepala">
            <span class="label-seksi">Pilihan Lapangan</span>
            <h2 class="judul-seksi">Lapangan Favorit</h2>
        </div>

        <div class="grid-kartu">
            @foreach ($unggulan as $field)
                <x-field-card :field="$field" />
            @endforeach
        </div>

        <div class="bawah-daftar">
            <a href="{{ route('lapangan.index') }}" class="tombol-hijau tombol-selengkapnya">Lihat Selengkapnya →</a>
        </div>
    </section>

    <section id="features" class="wadah seksi seksi-fasilitas">
        <div class="seksi-kepala tengah">
            <span class="label-seksi">Kenapa Memilih Kami</span>
            <h2 class="judul-seksi">Keunggulan Garuda Arena</h2>
        </div>
        <div class="grid-keunggulan">
            @foreach ($fasilitas as $item)
                <div class="kartu-keunggulan">
                    <div class="keunggulan-ikon">
                        <x-ikon-keunggulan :nama="$item['ikon']" />
                    </div>
                    <h3 class="keunggulan-judul">{{ $item['judul'] }}</h3>
                    <p class="keunggulan-teks">{{ $item['teks'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="gallery" class="wadah seksi seksi-galeri">
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

        <div class="bawah-daftar">
            <a href="{{ route('galeri.index') }}" class="tombol-hijau tombol-selengkapnya">Lihat Galeri Lengkap →</a>
        </div>
    </section>
</x-app-layout>
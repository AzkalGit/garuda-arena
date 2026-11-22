<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
        <link rel="stylesheet" href="{{ asset('css/ulasan.css') }}">
    </x-slot>

    <x-slot name="header">
        <nav class="remah" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="remah-link">Beranda</a>
            <span>/</span>
            <a href="{{ route('lapangan.index') }}" class="remah-link">Daftar Lapangan</a>
            <span>/</span>
            <span class="remah-aktif">{{ $field->name }}</span>
        </nav>
        <h1 class="detail-judul">{{ $field->name }}</h1>
    </x-slot>

    <div class="wadah detail">
        <div class="detail-grid">

            {{-- Kolom kiri: detail --}}
            <div class="detail-kiri">
                <div class="kartu-detail foto">
                    <div class="detail-foto">
                        <img src="{{ $field->imageUrl() }}" alt="Lapangan {{ $field->name }}">
                    </div>
                </div>

                <div class="kartu-detail">
                    <div class="detail-baris">
                        <span class="lencana-jenis">{{ $field->typeLabel() }}</span>
                        <span class="detail-rating">★ {{ $field->ratingLabel() }}
                            <span class="detail-ulasan">({{ $field->reviews_count }} ulasan)</span>
                        </span>
                    </div>
                    <p class="detail-lokasi">{{ $field->location }}</p>
                </div>

                <div class="kartu-detail">
                    <h2 class="detail-subjudul">Tentang Lapangan</h2>
                    <p class="detail-deskripsi">{{ $field->description }}</p>
                </div>

                <div class="kartu-detail longgar">
                    <h2 class="detail-subjudul">Spesifikasi Lapangan</h2>
                    <div class="grid-spesifikasi">
                        @foreach ($spesifikasi as $item)
                            <div class="kotak-spesifikasi">
                                <div class="spesifikasi-label">{{ $item['label'] }}</div>
                                <div class="spesifikasi-nilai">{{ $item['nilai'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="kartu-detail longgar">
                    <h2 class="detail-subjudul">Fasilitas Tersedia</h2>
                    <div class="daftar-fasilitas">
                        @foreach ($fasilitas as $item)
                            <span class="chip-fasilitas"><x-ikon-fasilitas :nama="$item" /> {{ $item }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="kartu-detail longgar">
                    <h2 class="detail-subjudul" id="ulasan">Ulasan Pelanggan <span class="ulasan-jumlah">({{ $jumlahUlasan }})</span></h2>
                    @forelse ($ulasan as $butir)
                        @if ($loop->first)
                            <ul class="ulasan-daftar">
                        @endif

                        <li class="ulasan-butir">
                            <div class="ulasan-kepala">
                                <span class="ulasan-nama">{{ $butir->user->name }}</span>
                                <span class="ulasan-bintang" aria-label="Rating {{ $butir->rating }} dari 5">{{ $butir->bintangLabel() }}</span>
                                <span class="ulasan-tanggal">{{ $butir->tanggalLabel() }}</span>
                            </div>
                            <p class="ulasan-isi">{{ $butir->comment }}</p>
                        </li>

                        @if ($loop->last)
                            </ul>
                        @endif
                    @empty
                        <p class="ulasan-kosong">Belum ada ulasan untuk lapangan ini.</p>
                    @endforelse

                    @if ($jumlahUlasan > $ulasanAwal)
                        <div class="ulasan-lebih">
                            @if ($semuaUlasan)
                                <a href="{{ route('lapangan.show', ['field' => $field, 'tanggal' => $tanggal]) }}#ulasan" class="ulasan-tautan">Tampilkan lebih sedikit</a>
                            @else
                                <a href="{{ route('lapangan.show', ['field' => $field, 'tanggal' => $tanggal, 'ulasan' => 'semua']) }}#ulasan" class="ulasan-tautan">Lihat selengkapnya ({{ $jumlahUlasan }} ulasan)</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom kanan: kartu harga dan booking --}}
            <div class="detail-kanan">
                <div class="kartu-booking">
                    <div class="tarif">
                        <div>
                            <span class="tarif-label">Tarif Sewa</span>
                            <div class="tarif-nilai">{{ $field->priceLabel() }}</div>
                        </div>
                        <span class="tarif-satuan">/ jam</span>
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="kotak-galat">
                            <ul class="daftar-galat">
                                @foreach ($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Pilih tanggal (GET, tanpa JavaScript) --}}
                    <form method="GET" action="{{ route('lapangan.show', $field) }}" class="form-tanggal">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <div class="baris-tanggal">
                            <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" min="{{ $hariIni }}" class="input-tanggal">
                            <button type="submit" class="tombol-cek">Cek</button>
                        </div>
                        <p class="catatan-kecil">{{ $tanggalLabel }}</p>
                    </form>

                    {{-- Booking (POST) --}}
                    <form method="POST" action="{{ route('booking.store') }}" class="form-booking">
                        @csrf
                        <input type="hidden" name="field_id" value="{{ $field->id }}">
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                        <fieldset class="kelompok-pilihan">
                            <legend class="kelompok-judul">Jam mulai</legend>
                            <div class="grid-pilihan">
                                @foreach ($slot as $s)
                                    <label class="pilihan">
                                        <input type="radio" name="start_hour" value="{{ $s['jam'] }}" class="pilihan-input"
                                               @disabled(! $s['tersedia'] || $mode !== 'pelanggan')
                                               @checked((int) old('start_hour') === $s['jam'])>
                                        <span class="pilihan-kotak">
                                            {{ $s['label'] }}
                                            @if ($s['keterangan'])
                                                <span class="pilihan-keterangan">{{ $s['keterangan'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="kelompok-pilihan">
                            <legend class="kelompok-judul">Durasi</legend>
                            <div class="grid-pilihan">
                                @foreach ($durasiOpsi as $d)
                                    <label class="pilihan">
                                        <input type="radio" name="duration" value="{{ $d }}" class="pilihan-input"
                                               @disabled($mode !== 'pelanggan')
                                               @checked((int) old('duration', 1) === $d)>
                                        <span class="pilihan-kotak">{{ $d }} jam</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="catatan-kecil">Total = tarif per jam × durasi. Durasi yang menabrak jam terisi atau melewati {{ sprintf('%02d:00', config('garuda.close_hour')) }} akan ditolak.</p>
                        </fieldset>

                        @if ($mode === 'pelanggan')
                            <button type="submit" class="tombol-booking">Booking Sekarang</button>
                        @elseif ($mode === 'tamu')
                            <a href="{{ route('login') }}" class="tombol-booking">Masuk untuk Booking</a>
                        @else
                            <p class="catatan-tengah">Akun admin tidak dapat membuat booking.</p>
                        @endif
                    </form>

                    <p class="catatan-kaki">
                        Pembayaran lewat transfer manual. Slot ditahan 1 jam setelah booking.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
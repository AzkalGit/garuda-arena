<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/ulasan.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">Riwayat Booking</h1>
            <p class="riwayat-keterangan">Pantau status pembayaran dan jadwal main kamu.</p>
        </div>

        @if (session('riwayat_pesan'))
            <div class="riwayat-pesan">{{ session('riwayat_pesan') }}</div>
        @endif

        @error('booking')
            <div class="riwayat-galat">{{ $message }}</div>
        @enderror

        <nav class="filter-status" aria-label="Filter status booking">
            @foreach ($tab as $item)
                <a href="{{ $item['url'] }}" @class(['filter-tombol', 'aktif' => $item['aktif']])>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        @forelse ($bookings as $booking)
            @if ($loop->first)
                <div class="riwayat-daftar">
            @endif

            <div class="riwayat-butir">
                <article class="kartu-riwayat">
                    <img src="{{ $booking->field->imageUrl() }}" alt="{{ $booking->field->name }}" class="riwayat-foto">

                    <div class="riwayat-info">
                        <div class="riwayat-info-atas">
                            <h2 class="riwayat-lapangan">{{ $booking->field->name }}</h2>
                            <span class="lencana-status status-{{ $booking->statusKelas() }}">{{ $booking->statusLabel() }}</span>
                        </div>
                        <div class="riwayat-waktu">{{ $booking->tanggalLabel() }} • {{ $booking->jamLabel() }}</div>
                        <div class="riwayat-kode">Kode {{ $booking->code }}</div>
                    </div>

                    <div class="riwayat-aksi">
                        <div class="riwayat-total">{{ $booking->totalLabel() }}</div>

                        @if ($booking->bisaDibayar())
                            <a href="{{ route('pembayaran.show', ['booking' => $booking->code]) }}" class="tombol-bayar">Bayar sekarang</a>
                            <form method="POST" action="{{ route('riwayat.batal', ['booking' => $booking->code]) }}" class="form-batal">
                                @csrf
                                <button type="submit" class="tombol-batal" onclick="return confirm('Batalkan booking ini?')">Batalkan</button>
                            </form>
                        @else
                            <a href="{{ route('pembayaran.show', ['booking' => $booking->code]) }}" class="tombol-detail">Lihat detail</a>
                        @endif
                    </div>
                </article>

                @if ($booking->review)
                    <section class="ulasan-panel ulasan-terkirim" aria-label="Ulasan kamu">
                        <div class="ulasan-kepala">
                            <span class="ulasan-judul">Ulasan kamu</span>
                            <span class="ulasan-bintang" aria-label="Rating {{ $booking->review->rating }} dari 5">{{ $booking->review->bintangLabel() }}</span>
                            <span class="ulasan-tanggal">{{ $booking->review->tanggalLabel() }}</span>
                        </div>
                        <p class="ulasan-isi">{{ $booking->review->comment }}</p>
                    </section>
                @elseif ($booking->bisaDiulas())
                    <section class="ulasan-panel" aria-label="Tulis ulasan">
                        <h3 class="ulasan-judul">Tulis ulasan</h3>
                        <p class="ulasan-petunjuk">Bagaimana pengalaman main kamu di {{ $booking->field->name }}?</p>

                        <form method="POST" action="{{ route('riwayat.ulasan', ['booking' => $booking->code]) }}" class="ulasan-form">
                            @csrf
                            <input type="hidden" name="kode" value="{{ $booking->code }}">

                            <fieldset class="bintang-pilih">
                                <legend class="bintang-judul">Rating</legend>
                                @foreach ([5, 4, 3, 2, 1] as $nilai)
                                    <input type="radio" name="rating" value="{{ $nilai }}" id="bintang-{{ $booking->code }}-{{ $nilai }}" class="bintang-input" required
                                           @checked(old('kode') === $booking->code && (int) old('rating') === $nilai)>
                                    <label for="bintang-{{ $booking->code }}-{{ $nilai }}" class="bintang-label" title="{{ $nilai }} bintang">★<span class="bintang-sr">{{ $nilai }} bintang</span></label>
                                @endforeach
                            </fieldset>

                            @if (old('kode') === $booking->code)
                                @error('rating')
                                    <p class="ulasan-galat">{{ $message }}</p>
                                @enderror
                            @endif

                            <label for="ulasan-{{ $booking->code }}" class="ulasan-label">Komentar</label>
                            <textarea name="comment" id="ulasan-{{ $booking->code }}" rows="3" maxlength="500" class="ulasan-isian" required placeholder="Ceritakan kondisi lapangan, fasilitas, dan pelayanannya.">{{ old('kode') === $booking->code ? old('comment') : '' }}</textarea>

                            @if (old('kode') === $booking->code)
                                @error('comment')
                                    <p class="ulasan-galat">{{ $message }}</p>
                                @enderror
                            @endif

                            <button type="submit" class="ulasan-tombol">Kirim ulasan</button>
                        </form>
                    </section>
                @endif
            </div>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <div class="riwayat-kosong">
                <h2 class="riwayat-kosong-judul">Belum ada booking</h2>
                <p class="riwayat-kosong-isi">Belum ada booking yang cocok dengan filter ini.</p>
                <a href="{{ route('lapangan.index') }}" class="tombol-bayar">Lihat Daftar Lapangan</a>
            </div>
        @endforelse
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
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
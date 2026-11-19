<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/galeri.css') }}">
    </x-slot>

    <x-slot name="header">
        <h1 class="galeri-judul">Galeri Lapangan</h1>
        <p class="galeri-subjudul">Lihat suasana lapangan Garuda Arena. Klik foto untuk memperbesar.</p>
    </x-slot>

    <div class="wadah galeri" x-data="{ foto: null }" @keydown.escape.window="foto = null">
        <nav class="galeri-filter" aria-label="Filter jenis lantai">
            @foreach ($tab as $item)
                <a href="{{ $item['url'] }}" @class(['galeri-tab', 'aktif' => $item['aktif']])>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <p class="galeri-jumlah">Menampilkan {{ count($daftar) }} foto</p>

        @if (count($daftar) === 0)
            <div class="galeri-kosong">
                <h2 class="galeri-kosong-judul">Belum ada foto</h2>
                <p class="galeri-kosong-teks">Tidak ada lapangan aktif untuk jenis lantai ini.</p>
                <a href="{{ route('galeri.index') }}" class="tombol-hijau">Lihat Semua</a>
            </div>
        @else
            <div class="galeri-grid">
                @foreach ($daftar as $item)
                    <button type="button" class="galeri-item" @click="foto = @js($item)" aria-label="Perbesar foto {{ $item['nama'] }}">
                        <img src="{{ $item['foto'] }}" alt="Lapangan {{ $item['nama'] }}" loading="lazy">
                        <span class="galeri-jenis">{{ $item['jenis'] }}</span>
                        <span class="galeri-keterangan">
                            <span class="galeri-nama">{{ $item['nama'] }}</span>
                            <span class="galeri-lokasi">{{ $item['lokasi'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        @endif

        <div x-show="foto" x-cloak class="galeri-lapisan" role="dialog" aria-modal="true" aria-label="Foto lapangan diperbesar" @click.self="foto = null">
            <template x-if="foto">
                <div class="galeri-besar">
                    <button type="button" class="galeri-tutup" @click="foto = null" aria-label="Tutup">&times;</button>
                    <img :src="foto.foto" :alt="'Lapangan ' + foto.nama">
                    <div class="galeri-besar-teks">
                        <strong x-text="foto.nama"></strong>
                        <span x-text="foto.lokasi + ' · ' + foto.jenis"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-app-layout>
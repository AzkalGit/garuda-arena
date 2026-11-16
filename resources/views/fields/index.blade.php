<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/katalog.css') }}">
    </x-slot>

    <x-slot name="header">
        <h1 class="katalog-judul">Daftar Lapangan</h1>
        <p class="katalog-subjudul">Temukan lapangan futsal berkualitas dengan fasilitas lengkap dan harga terjangkau.</p>
    </x-slot>

    <div class="wadah katalog">
        <form method="GET" action="{{ route('lapangan.index') }}" class="filter-katalog">
            <div>
                <label for="jenis" class="filter-label">Jenis Lantai</label>
                <select id="jenis" name="jenis" class="filter-pilihan">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($jenis === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="harga" class="filter-label">Rentang Harga</label>
                <select id="harga" name="harga" class="filter-pilihan">
                    <option value="">Semua Harga</option>
                    @foreach ($hargaOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($harga === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="urut" class="filter-label">Urutkan</label>
                <select id="urut" name="urut" class="filter-pilihan">
                    @foreach ($urutOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($urut === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-aksi">
                <button type="submit" class="tombol-hijau tombol-terapkan">Terapkan</button>
                <a href="{{ route('lapangan.index') }}" class="tombol-reset">Reset</a>
            </div>
        </form>

        <p class="katalog-jumlah">Menampilkan {{ $fields->count() }} dari {{ $total }} lapangan</p>

        @if ($fields->isEmpty())
            <div class="katalog-kosong">
                <h3 class="kosong-judul">Tidak ada lapangan yang cocok</h3>
                <p class="kosong-teks">Coba ubah filter untuk menemukan lapangan yang tersedia.</p>
                <a href="{{ route('lapangan.index') }}" class="tombol-hijau kosong-tombol">Reset Filter</a>
            </div>
        @else
            <div class="grid-katalog">
                @foreach ($fields as $field)
                    <x-field-card :field="$field" />
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
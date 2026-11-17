<x-admin-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-booking.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-lapangan.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala kepala-dengan-tombol">
            <div>
                <h1 class="riwayat-judul">Kelola Lapangan</h1>
                <p class="riwayat-keterangan">Tambah, ubah, dan atur status lapangan. Lapangan nonaktif tidak bisa dipesan, tetapi booking yang sudah ada tetap berjalan.</p>
            </div>
            <a href="{{ route('admin.fields.create') }}" class="admin-tombol-tambah">Tambah Lapangan</a>
        </div>

        @if (session('admin_pesan'))
            <div class="riwayat-pesan">{{ session('admin_pesan') }}</div>
        @endif

        <nav class="filter-status" aria-label="Filter status lapangan">
            @foreach ($tab as $item)
                <a href="{{ $item['url'] }}" @class(['filter-tombol', 'aktif' => $item['aktif']])>{{ $item['label'] }} ({{ $item['jumlah'] }})</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.fields.index') }}" class="admin-cari">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="search" name="q" value="{{ $cari }}" placeholder="Cari nama atau lokasi lapangan" class="admin-cari-isi">
            <button type="submit" class="admin-tombol-cari">Cari</button>
        </form>

        <div class="admin-tabel-bungkus">
            <table class="admin-tabel">
                <thead>
                    <tr>
                        <th>Lapangan</th>
                        <th>Jenis</th>
                        <th>Tarif</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fields as $field)
                        <tr>
                            <td>
                                <div class="admin-lapangan-baris">
                                    <img src="{{ $field->imageUrl() }}" alt="" class="admin-foto">
                                    <div>
                                        <div class="admin-kode">{{ $field->name }}</div>
                                        <div class="admin-kecil">{{ $field->size }} · {{ $field->capacity }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $field->typeLabel() }}</td>
                            <td>{{ $field->priceLabel() }} / jam</td>
                            <td>{{ $field->location }}</td>
                            <td><span class="lencana-lapangan lapangan-{{ $field->status }}">{{ $field->statusLabel() }}</span></td>
                            <td>
                                <div class="admin-aksi">
                                    <a href="{{ route('admin.fields.edit', ['field' => $field]) }}" class="admin-tombol-bukti">Ubah</a>
                                    <form method="POST" action="{{ route('admin.fields.status', ['field' => $field]) }}">
                                        @csrf
                                        <button type="submit" @class(['admin-tombol-tolak' => $field->isActive(), 'admin-tombol-setuju' => ! $field->isActive()])>
                                            {{ $field->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-kosong">Tidak ada lapangan yang cocok dengan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
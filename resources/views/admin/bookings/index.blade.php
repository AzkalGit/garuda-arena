<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-booking.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">Kelola Booking</h1>
            <p class="riwayat-keterangan">Verifikasi bukti transfer, lalu setujui atau tolak pesanan.</p>
        </div>

        @if (session('admin_pesan'))
            <div class="riwayat-pesan">{{ session('admin_pesan') }}</div>
        @endif

        @error('booking')
            <div class="riwayat-galat">{{ $message }}</div>
        @enderror

        @error('alasan')
            <div class="riwayat-galat">{{ $message }}</div>
        @enderror

        <nav class="filter-status" aria-label="Filter status booking">
            @foreach ($tab as $item)
                <a href="{{ $item['url'] }}" @class(['filter-tombol', 'aktif' => $item['aktif']])>{{ $item['label'] }} ({{ $item['jumlah'] }})</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.bookings.index') }}" class="admin-cari">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="search" name="q" value="{{ $cari }}" placeholder="Cari kode, nama pelanggan, atau lapangan" class="admin-cari-isi">
            <button type="submit" class="admin-tombol-cari">Cari</button>
        </form>

        <div class="admin-tabel-bungkus">
            <table class="admin-tabel">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Lapangan</th>
                        <th>Jadwal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="admin-kode">{{ $booking->code }}</td>
                            <td>{{ $booking->user->name }}</td>
                            <td>{{ $booking->field->name }}</td>
                            <td>{{ $booking->tanggalLabel() }}<br><span class="admin-kecil">{{ $booking->jamLabel() }}</span></td>
                            <td>{{ $booking->totalLabel() }}</td>
                            <td>
                                <span class="lencana-status status-{{ $booking->statusKelas() }}">{{ $booking->statusLabel() }}</span>
                                @if ($booking->rejection_reason)
                                    <div class="admin-kecil">Ditolak: {{ $booking->rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="admin-aksi">
                                    @if ($booking->proof_name)
                                        <a href="{{ route('admin.bookings.bukti', ['booking' => $booking->code]) }}" target="_blank" class="admin-tombol-bukti">Lihat bukti</a>
                                    @endif

                                    @if ($booking->bisaDiverifikasi())
                                        <form method="POST" action="{{ route('admin.bookings.setujui', ['booking' => $booking->code]) }}">
                                            @csrf
                                            <button type="submit" class="admin-tombol-setuju">Setujui</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.bookings.tolak', ['booking' => $booking->code]) }}" class="admin-form-tolak">
                                            @csrf
                                            <input type="text" name="alasan" placeholder="Alasan penolakan" maxlength="255" required class="admin-isi-alasan">
                                            <button type="submit" class="admin-tombol-tolak">Tolak</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-kosong">Tidak ada booking yang cocok dengan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
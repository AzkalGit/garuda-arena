<x-admin-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-booking.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-pengguna.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">Kelola Pengguna</h1>
            <p class="riwayat-keterangan">Daftar akun terdaftar ({{ $total }} pengguna).</p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-cari">
            <input type="search" name="q" value="{{ $cari }}" placeholder="Cari nama atau email" class="admin-cari-isi">
            <button type="submit" class="admin-tombol-cari">Cari</button>
        </form>

        <div class="admin-tabel-bungkus">
            <table class="admin-tabel">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Peran</th>
                        <th>Booking</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <span @class(['lencana-peran', 'lencana-peran-admin' => $user->isAdmin(), 'lencana-peran-pelanggan' => ! $user->isAdmin()])>{{ $user->peranLabel() }}</span>
                            </td>
                            <td>{{ $user->bookings_count }}</td>
                            <td>{{ $user->bergabungLabel() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-kosong">Tidak ada pengguna yang cocok dengan pencarian ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
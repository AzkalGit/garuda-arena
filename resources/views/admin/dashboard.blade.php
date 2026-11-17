<x-admin-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-booking.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">Dashboard</h1>
            <p class="riwayat-keterangan">Ringkasan aktivitas Garuda Arena hari ini.</p>
        </div>

        <div class="dashboard-kartu-grid">
            <div class="dashboard-kartu">
                <div class="dashboard-ikon dashboard-ikon-hijau">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                </div>
                <div>
                    <div class="dashboard-kartu-label">Booking hari ini</div>
                    <div class="dashboard-kartu-nilai">{{ $bookingHariIni }}</div>
                </div>
            </div>

            <div class="dashboard-kartu">
                <div class="dashboard-ikon dashboard-ikon-aksen">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </div>
                <div>
                    <div class="dashboard-kartu-label">Menunggu verifikasi</div>
                    <div class="dashboard-kartu-nilai">{{ $menungguVerifikasi }}</div>
                </div>
            </div>

            <div class="dashboard-kartu">
                <div class="dashboard-ikon dashboard-ikon-tua">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 15h2"/></svg>
                </div>
                <div>
                    <div class="dashboard-kartu-label">Pendapatan terverifikasi</div>
                    <div class="dashboard-kartu-nilai">{{ $pendapatanLabel }}</div>
                </div>
            </div>

            <div class="dashboard-kartu">
                <div class="dashboard-ikon dashboard-ikon-hijau">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M12 5v14"/><circle cx="12" cy="12" r="2.5"/></svg>
                </div>
                <div>
                    <div class="dashboard-kartu-label">Lapangan aktif</div>
                    <div class="dashboard-kartu-nilai">{{ $lapanganAktifLabel }}</div>
                </div>
            </div>
        </div>

        <div class="dashboard-bawah">
            <section class="dashboard-panel">
                <h2 class="dashboard-panel-judul">Booking 7 hari terakhir</h2>
                <x-grafik-batang :data="$grafik" judul="Jumlah booking 7 hari terakhir" />
            </section>

            <section class="dashboard-panel">
                <div class="dashboard-panel-kepala">
                    <h2 class="dashboard-panel-judul">Booking terbaru</h2>
                    <a href="{{ route('admin.bookings.index') }}" class="dashboard-tautan">Kelola booking</a>
                </div>

                <div class="admin-tabel-bungkus">
                    <table class="admin-tabel">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pelanggan</th>
                                <th>Lapangan</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terbaru as $booking)
                                <tr>
                                    <td class="admin-kode">{{ $booking->code }}</td>
                                    <td>{{ $booking->user->name }}</td>
                                    <td>{{ $booking->field->name }}</td>
                                    <td>{{ $booking->tanggalLabel() }}<br><span class="admin-kecil">{{ $booking->jamLabel() }}</span></td>
                                    <td><span class="lencana-status status-{{ $booking->statusKelas() }}">{{ $booking->statusLabel() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="admin-kosong">Belum ada booking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
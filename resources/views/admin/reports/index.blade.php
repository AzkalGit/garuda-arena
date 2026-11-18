<x-admin-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-booking.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-laporan.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">Laporan</h1>
            <p class="riwayat-keterangan">Ringkasan penggunaan lapangan dan pendapatan. Hanya booking yang dikonfirmasi dan selesai yang dihitung.</p>
        </div>

        <div class="laporan-tab-baris">
            @foreach ($tab as $t)
                <a href="{{ $t['url'] }}" @class(['laporan-tab', 'aktif' => $t['aktif']])>{{ $t['label'] }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="laporan-form">
            <input type="hidden" name="periode" value="{{ $periode }}">

            @if ($periode === 'kustom')
                <label class="laporan-label">
                    Dari
                    <input type="date" name="dari" value="{{ $dari }}" class="laporan-input">
                </label>
                <label class="laporan-label">
                    Sampai
                    <input type="date" name="sampai" value="{{ $sampai }}" class="laporan-input">
                </label>
            @else
                <label class="laporan-label">
                    Tanggal acuan
                    <input type="date" name="tanggal" value="{{ $acuan }}" class="laporan-input">
                </label>
            @endif

            <button type="submit" class="laporan-tombol">Terapkan</button>

            <a href="{{ $urlCsv }}" class="laporan-unduh">
                <svg class="laporan-unduh-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7 11l5 5 5-5M5 20h14"/></svg>
                Unduh CSV
            </a>
        </form>

        <div class="laporan-kartu-grid">
            @foreach ($kartu as $k)
                <div class="laporan-kartu">
                    <div class="dashboard-kartu-label">{{ $k['label'] }}</div>
                    <div class="laporan-kartu-nilai">{{ $k['nilai'] }}</div>
                    @if ($k['catatan'])
                        <div class="laporan-kartu-catatan {{ $k['kelas'] }}">{{ $k['catatan'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <section class="dashboard-panel laporan-panel">
            <h2 class="dashboard-panel-judul">Per lapangan</h2>

            <div class="admin-tabel-bungkus">
                <table class="admin-tabel">
                    <thead>
                        <tr>
                            <th>Lapangan</th>
                            <th>Jenis</th>
                            <th class="laporan-angka">Jumlah booking</th>
                            <th class="laporan-angka">Jam terpakai</th>
                            <th class="laporan-angka">Tarif / jam</th>
                            <th class="laporan-angka">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($baris as $b)
                            <tr>
                                <td>{{ $b['nama'] }}</td>
                                <td>{{ $b['jenis'] }}</td>
                                <td class="laporan-angka">{{ $b['jumlah'] }}</td>
                                <td class="laporan-angka">{{ $b['jam'] }}</td>
                                <td class="laporan-angka">{{ $b['tarifLabel'] }}</td>
                                <td class="laporan-angka">{{ $b['pendapatanLabel'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-kosong">Belum ada lapangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($baris->isNotEmpty())
                        <tfoot>
                            <tr class="laporan-total">
                                <td colspan="2">Total</td>
                                <td class="laporan-angka">{{ $totalBaris['jumlah'] }}</td>
                                <td class="laporan-angka">{{ $totalBaris['jam'] }}</td>
                                <td></td>
                                <td class="laporan-angka">{{ $totalBaris['pendapatanLabel'] }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
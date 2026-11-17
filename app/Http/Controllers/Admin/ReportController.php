<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class ReportController extends Controller
{
    private const PERIODE = [
        'minggu' => 'Minggu',
        'bulan' => 'Bulan',
        'tahun' => 'Tahun',
        'kustom' => 'Kustom',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('kelola', Booking::class);

        Booking::perbaruiStatus();

        return view('admin.reports.index', $this->susun($request));
    }

    public function csv(Request $request): Response
    {
        Gate::authorize('kelola', Booking::class);

        Booking::perbaruiStatus();

        $d = $this->susun($request);

        $isi = "\xEF\xBB\xBF";

        foreach ($d['ringkasanCsv'] as $baris) {
            $isi .= $this->barisCsv($baris);
        }

        $isi .= $this->barisCsv([]);
        $isi .= $this->barisCsv(['Lapangan', 'Jenis', 'Jumlah booking', 'Jam terpakai', 'Tarif per jam (Rp)', 'Pendapatan (Rp)']);

        foreach ($d['baris'] as $b) {
            $isi .= $this->barisCsv([$b['nama'], $b['jenis'], $b['jumlah'], $b['jam'], $b['tarif'], $b['pendapatan']]);
        }

        $nama = 'laporan-garuda-arena-'.$d['dari'].'-sampai-'.$d['sampai'].'.csv';

        return response($isi, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nama.'"',
        ]);
    }

    private function susun(Request $request): array
    {
        $hariIni = Carbon::today();

        $periode = $request->query('periode');

        if (! is_string($periode) || ! array_key_exists($periode, self::PERIODE)) {
            $periode = 'bulan';
        }

        $acuan = $this->tanggal($request->query('tanggal')) ?? $hariIni->copy();

        if ($periode === 'kustom') {
            $dari = $this->tanggal($request->query('dari')) ?? $hariIni->copy()->startOfMonth();
            $sampai = $this->tanggal($request->query('sampai')) ?? $hariIni->copy();

            if ($sampai->lt($dari)) {
                $sampai = $dari->copy();
            }

            if ($sampai->gt($dari->copy()->addDays(365))) {
                $sampai = $dari->copy()->addDays(365);
            }

            $jumlahHari = (int) round($dari->diffInDays($sampai)) + 1;
            $sebelumSampai = $dari->copy()->subDay();
            $sebelumDari = $sebelumSampai->copy()->subDays($jumlahHari - 1);
        } elseif ($periode === 'minggu') {
            $dari = $acuan->copy()->startOfWeek();
            $sampai = $acuan->copy()->endOfWeek()->startOfDay();
            $sebelumDari = $dari->copy()->subWeek();
            $sebelumSampai = $sebelumDari->copy()->endOfWeek()->startOfDay();
        } elseif ($periode === 'tahun') {
            $dari = $acuan->copy()->startOfYear();
            $sampai = $acuan->copy()->endOfYear()->startOfDay();
            $sebelumDari = $dari->copy()->subYear()->startOfYear();
            $sebelumSampai = $sebelumDari->copy()->endOfYear()->startOfDay();
        } else {
            $dari = $acuan->copy()->startOfMonth();
            $sampai = $acuan->copy()->endOfMonth()->startOfDay();
            $sebelumDari = $dari->copy()->subMonthNoOverflow()->startOfMonth();
            $sebelumSampai = $sebelumDari->copy()->endOfMonth()->startOfDay();
        }

        $statusHitung = [Booking::STATUS_DIKONFIRMASI, Booking::STATUS_SELESAI];

        $bookings = $this->rentang($dari, $sampai)->whereIn('status', $statusHitung)->get();
        $sebelum = $this->rentang($sebelumDari, $sebelumSampai)->whereIn('status', $statusHitung)->get();
        $pembatalan = $this->rentang($dari, $sampai)->where('status', Booking::STATUS_DIBATALKAN)->count();

        $total = $bookings->count();
        $pendapatan = (int) $bookings->sum('total');
        $jam = (int) $bookings->sum('duration');
        $rataRata = $total > 0 ? (int) round($pendapatan / $total) : 0;

        // Tabel per lapangan (semua lapangan, termasuk yang 0 booking).
        $perLapangan = $bookings->groupBy('field_id');

        $baris = Field::query()->orderBy('name')->get()->map(function (Field $field) use ($perLapangan) {
            $kumpulan = $perLapangan->get($field->id, collect());
            $pendapatanField = (int) $kumpulan->sum('total');

            return [
                'nama' => $field->name,
                'jenis' => $field->typeLabel(),
                'jumlah' => $kumpulan->count(),
                'jam' => (int) $kumpulan->sum('duration'),
                'tarif' => (int) $field->price_per_hour,
                'tarifLabel' => $this->rupiah((int) $field->price_per_hour),
                'pendapatan' => $pendapatanField,
                'pendapatanLabel' => $this->rupiah($pendapatanField),
            ];
        })->values();

        $terlaris = $baris
            ->filter(fn (array $b) => $b['jumlah'] > 0)
            ->sortBy([['jumlah', 'desc'], ['pendapatan', 'desc']])
            ->first();

        $jamSibuk = null;

        if ($total > 0) {
            $hitungJam = $bookings->countBy('start_hour');
            $maks = $hitungJam->max();
            $jamSibuk = $hitungJam->filter(fn ($n) => $n === $maks)->keys()->min();
        }

        $jamSibukLabel = $jamSibuk === null ? '-' : sprintf('%02d.00', $jamSibuk);

        // Perbandingan pendapatan dengan periode sebelumnya.
        $pendapatanSebelum = (int) $sebelum->sum('total');
        $banding = null;
        $bandingKelas = '';

        if ($pendapatanSebelum === 0) {
            $banding = $pendapatan > 0
                ? 'Periode sebelumnya belum ada pendapatan'
                : 'Belum ada pendapatan di periode ini maupun sebelumnya';
        } else {
            $persen = (int) round(($pendapatan - $pendapatanSebelum) / $pendapatanSebelum * 100);
            $banding = ($persen > 0 ? '+' : '').$persen.'% dibanding periode sebelumnya';
            $bandingKelas = $persen > 0 ? 'laporan-naik' : ($persen < 0 ? 'laporan-turun' : '');
        }

        // Tren: per hari bila rentang <= 31 hari, selain itu per bulan.
        $perHari = $sampai->lte($dari->copy()->addDays(30));
        $tujuhHari = $sampai->lte($dari->copy()->addDays(6));
        $satuTahun = $dari->year === $sampai->year;

        $hitungTren = $bookings->countBy(
            fn (Booking $b) => $perHari ? $b->date->toDateString() : $b->date->format('Y-m')
        );

        $grafik = [];
        $kursor = $perHari ? $dari->copy() : $dari->copy()->startOfMonth();

        while ($kursor->lte($sampai)) {
            $kunci = $perHari ? $kursor->toDateString() : $kursor->format('Y-m');

            if ($perHari) {
                $label = $tujuhHari
                    ? $kursor->copy()->locale('id')->translatedFormat('D j')
                    : $kursor->format('j');
            } else {
                $label = $kursor->copy()->locale('id')->translatedFormat($satuTahun ? 'M' : 'M y');
            }

            $grafik[] = ['label' => $label, 'nilai' => (int) ($hitungTren[$kunci] ?? 0)];

            $perHari ? $kursor->addDay() : $kursor->addMonthNoOverflow();
        }

        // Label periode.
        if ($periode === 'bulan') {
            $periodeLabel = $dari->copy()->locale('id')->translatedFormat('F Y');
        } elseif ($periode === 'tahun') {
            $periodeLabel = $dari->format('Y');
        } else {
            $periodeLabel = $dari->copy()->locale('id')->translatedFormat('j M Y')
                .' – '.$sampai->copy()->locale('id')->translatedFormat('j M Y');
        }

        $kartu = [
            ['label' => 'Periode', 'nilai' => $periodeLabel, 'catatan' => null, 'kelas' => ''],
            ['label' => 'Booking terverifikasi', 'nilai' => (string) $total, 'catatan' => null, 'kelas' => ''],
            ['label' => 'Pendapatan', 'nilai' => $this->rupiah($pendapatan), 'catatan' => $banding, 'kelas' => $bandingKelas],
            ['label' => 'Jam terpakai', 'nilai' => $jam.' jam', 'catatan' => null, 'kelas' => ''],
            ['label' => 'Rata-rata per booking', 'nilai' => $this->rupiah($rataRata), 'catatan' => null, 'kelas' => ''],
            ['label' => 'Lapangan terlaris', 'nilai' => $terlaris['nama'] ?? '-', 'catatan' => null, 'kelas' => ''],
            ['label' => 'Jam tersibuk', 'nilai' => $jamSibukLabel, 'catatan' => null, 'kelas' => ''],
            ['label' => 'Pembatalan', 'nilai' => (string) $pembatalan, 'catatan' => null, 'kelas' => ''],
        ];

        $tab = [];

        foreach (self::PERIODE as $kunci => $label) {
            $param = $kunci === 'kustom'
                ? ['periode' => $kunci, 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]
                : ['periode' => $kunci, 'tanggal' => $acuan->toDateString()];

            $tab[] = [
                'label' => $label,
                'url' => route('admin.reports.index', $param),
                'aktif' => $periode === $kunci,
            ];
        }

        $paramCsv = $periode === 'kustom'
            ? ['periode' => $periode, 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]
            : ['periode' => $periode, 'tanggal' => $acuan->toDateString()];

        return [
            'periode' => $periode,
            'periodeLabel' => $periodeLabel,
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'acuan' => $acuan->toDateString(),
            'tab' => $tab,
            'kartu' => $kartu,
            'grafik' => $grafik,
            'grafikJudul' => $perHari ? 'Booking per hari' : 'Booking per bulan',
            'baris' => $baris,
            'totalBaris' => [
                'jumlah' => $total,
                'jam' => $jam,
                'pendapatanLabel' => $this->rupiah($pendapatan),
            ],
            'urlCsv' => route('admin.reports.csv', $paramCsv),
            'ringkasanCsv' => [
                ['Periode', $periodeLabel],
                ['Total booking', $total],
                ['Pendapatan (Rp)', $pendapatan],
                ['Jam terpakai', $jam],
                ['Rata-rata per booking (Rp)', $rataRata],
                ['Lapangan terlaris', $terlaris['nama'] ?? '-'],
                ['Jam tersibuk', $jamSibukLabel],
                ['Pembatalan', $pembatalan],
            ],
        ];
    }

    private function rentang(Carbon $dari, Carbon $sampai): Builder
    {
        return Booking::query()
            ->whereDate('date', '>=', $dari->toDateString())
            ->whereDate('date', '<=', $sampai->toDateString());
    }

    private function tanggal(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        try {
            $tanggal = Carbon::createFromFormat('!Y-m-d', $nilai);
        } catch (Throwable) {
            return null;
        }

        if ($tanggal === false || $tanggal->format('Y-m-d') !== $nilai) {
            return null;
        }

        return $tanggal;
    }

    private function rupiah(int $angka): string
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    private function barisCsv(array $baris): string
    {
        $kolom = array_map(function ($nilai) {
            $teks = (string) $nilai;

            if (preg_match('/[;"\r\n]/', $teks)) {
                $teks = '"'.str_replace('"', '""', $teks).'"';
            }

            return $teks;
        }, $baris);

        return implode(';', $kolom)."\r\n";
    }
}

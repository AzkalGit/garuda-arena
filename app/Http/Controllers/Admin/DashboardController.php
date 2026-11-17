<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Field;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('kelola', Booking::class);

        Booking::perbaruiStatus();

        $hariIni = Carbon::today();

        $bookingHariIni = Booking::query()
            ->whereDate('date', $hariIni->toDateString())
            ->where('status', '!=', Booking::STATUS_DIBATALKAN)
            ->count();

        $menungguVerifikasi = Booking::query()
            ->where('status', Booking::STATUS_MENUNGGU_VERIFIKASI)
            ->count();

        $pendapatan = (int) Booking::query()
            ->whereIn('status', [Booking::STATUS_DIKONFIRMASI, Booking::STATUS_SELESAI])
            ->sum('total');

        $lapanganAktif = Field::query()->where('status', Field::STATUS_AKTIF)->count();
        $lapanganTotal = Field::query()->count();

        $mulai = $hariIni->copy()->subDays(6);

        $perTanggal = Booking::query()
            ->whereDate('date', '>=', $mulai->toDateString())
            ->whereDate('date', '<=', $hariIni->toDateString())
            ->where('status', '!=', Booking::STATUS_DIBATALKAN)
            ->get(['date'])
            ->countBy(fn ($booking) => $booking->date->toDateString());

        $grafik = [];

        for ($i = 0; $i < 7; $i++) {
            $hari = $mulai->copy()->addDays($i);

            $grafik[] = [
                'label' => $hari->locale('id')->translatedFormat('D j'),
                'nilai' => (int) ($perTanggal[$hari->toDateString()] ?? 0),
            ];
        }

        $terbaru = Booking::query()
            ->with(['user', 'field'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'bookingHariIni' => $bookingHariIni,
            'menungguVerifikasi' => $menungguVerifikasi,
            'pendapatanLabel' => 'Rp '.number_format($pendapatan, 0, ',', '.'),
            'lapanganAktifLabel' => $lapanganAktif.' dari '.$lapanganTotal,
            'grafik' => $grafik,
            'terbaru' => $terbaru,
        ]);
    }
}

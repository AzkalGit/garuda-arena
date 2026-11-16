<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        Booking::perbaruiStatus();

        $daftarStatus = Booking::daftarStatus();
        $status = $request->query('status');

        if (! is_string($status) || ! array_key_exists($status, $daftarStatus)) {
            $status = null;
        }

        $bookings = Booking::query()
            ->with('field')
            ->where('user_id', $request->user()->id)
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $tab = [[
            'label' => 'Semua',
            'url' => route('riwayat.index'),
            'aktif' => $status === null,
        ]];

        foreach ($daftarStatus as $kunci => $label) {
            $tab[] = [
                'label' => $label,
                'url' => route('riwayat.index', ['status' => $kunci]),
                'aktif' => $status === $kunci,
            ];
        }

        return view('history.index', [
            'bookings' => $bookings,
            'tab' => $tab,
        ]);
    }

    public function batal(Booking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $booking->batalkan();

        return redirect()
            ->route('riwayat.index')
            ->with('riwayat_pesan', 'Booking '.$booking->code.' dibatalkan.');
    }
}

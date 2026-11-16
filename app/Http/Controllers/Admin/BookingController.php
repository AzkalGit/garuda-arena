<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookingRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola', Booking::class);

        Booking::perbaruiStatus();

        $daftarStatus = Booking::daftarStatus();
        $status = $request->query('status');

        if (! is_string($status) || ! array_key_exists($status, $daftarStatus)) {
            $status = null;
        }

        $cari = $request->query('q');
        $cari = is_string($cari) ? trim($cari) : '';

        $bookings = Booking::query()
            ->with(['user', 'field'])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($cari !== '', function ($query) use ($cari) {
                $pola = '%'.$cari.'%';

                $query->where(function ($sub) use ($pola) {
                    $sub->where('code', 'like', $pola)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $pola))
                        ->orWhereHas('field', fn ($f) => $f->where('name', 'like', $pola));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $jumlah = Booking::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tab = [[
            'label' => 'Semua',
            'jumlah' => (int) $jumlah->sum(),
            'url' => route('admin.bookings.index', array_filter(['q' => $cari])),
            'aktif' => $status === null,
        ]];

        foreach ($daftarStatus as $kunci => $label) {
            $tab[] = [
                'label' => $label,
                'jumlah' => (int) ($jumlah[$kunci] ?? 0),
                'url' => route('admin.bookings.index', array_filter(['status' => $kunci, 'q' => $cari])),
                'aktif' => $status === $kunci,
            ];
        }

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'tab' => $tab,
            'status' => $status,
            'cari' => $cari,
        ]);
    }

    public function setujui(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('verify', $booking);

        $booking->setujui($request->user());

        return redirect()
            ->route('admin.bookings.index')
            ->with('admin_pesan', 'Booking '.$booking->code.' dikonfirmasi.');
    }

    public function tolak(RejectBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->tolak($request->user(), $request->validated('alasan'));

        return redirect()
            ->route('admin.bookings.index')
            ->with('admin_pesan', 'Bukti booking '.$booking->code.' ditolak.');
    }

    public function bukti(Booking $booking): Response|RedirectResponse
    {
        Gate::authorize('verify', $booking);

        if ($booking->proof_path === null || ! Storage::disk('local')->exists($booking->proof_path)) {
            return redirect()
                ->route('admin.bookings.index')
                ->withErrors(['booking' => 'Berkas bukti untuk booking '.$booking->code.' tidak tersedia.']);
        }

        return Storage::disk('local')->response($booking->proof_path);
    }
}

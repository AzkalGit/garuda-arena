<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitProofRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        Booking::perbaruiStatus();
        $booking->refresh()->load('field');

        $tahap = match (true) {
            $booking->bisaDibayar() => 'bayar',
            $booking->status === Booking::STATUS_MENUNGGU_VERIFIKASI => 'verifikasi',
            in_array($booking->status, [Booking::STATUS_DIKONFIRMASI, Booking::STATUS_SELESAI], true) => 'dikonfirmasi',
            default => 'batal',
        };

        return view('payments.show', [
            'booking' => $booking,
            'tahap' => $tahap,
            'selesaiBayar' => in_array($tahap, ['verifikasi', 'dikonfirmasi'], true),
            'sisaMenit' => $booking->sisaMenit(),
        ]);
    }

    public function store(SubmitProofRequest $request, Booking $booking): RedirectResponse
    {
        $booking->kirimBukti($request->file('proof'));

        return redirect()->route('pembayaran.show', ['booking' => $booking->code]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        // Booking dikonfirmasi yang jam mainnya lewat menjadi "selesai" dulu.
        Booking::perbaruiStatus();

        Review::buat(
            $request->user(),
            $booking,
            (int) $request->validated('rating'),
            trim((string) $request->validated('comment')),
        );

        return redirect()
            ->route('riwayat.index')
            ->with('riwayat_pesan', 'Terima kasih! Ulasan untuk booking '.$booking->code.' sudah terkirim.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Field;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $field = Field::findOrFail($request->integer('field_id'));

        $booking = Booking::createPending(
            $request->user(),
            $field,
            Carbon::createFromFormat('!Y-m-d', $request->input('tanggal')),
            $request->integer('start_hour'),
            $request->integer('duration'),
        );

        return redirect()
            ->route('lapangan.show', ['field' => $field->id, 'tanggal' => $booking->date->toDateString()])
            ->with('booking_sukses', [
                'kode' => $booking->code,
                'tanggal' => $booking->date->locale('id')->translatedFormat('l, j F Y'),
                'jam' => $booking->jamLabel(),
                'total' => $booking->totalLabel(),
            ]);
    }
}

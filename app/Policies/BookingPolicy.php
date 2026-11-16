<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /** Melihat riwayat booking: hanya pelanggan (admin 403). */
    public function viewAny(User $user): bool
    {
        return ! $user->isAdmin();
    }

    /** Hanya pelanggan (bukan admin) yang boleh membuat booking. */
    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    /** Melihat halaman pembayaran: hanya pemilik, selain itu 404. */
    public function view(User $user, Booking $booking): Response
    {
        return $user->id === $booking->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /** Mengirim bukti bayar: hanya pemilik, selain itu 404. */
    public function pay(User $user, Booking $booking): Response
    {
        return $user->id === $booking->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /** Membatalkan booking: hanya pemilik, selain itu 404 (cek status ada di model). */
    public function cancel(User $user, Booking $booking): Response
    {
        return $user->id === $booking->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}

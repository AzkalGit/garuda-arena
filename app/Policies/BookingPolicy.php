<?php

namespace App\Policies;

use App\Models\User;

class BookingPolicy
{
    /** Hanya pelanggan (bukan admin) yang boleh membuat booking. */
    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }
}

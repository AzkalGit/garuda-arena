<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Melihat daftar pengguna di panel admin: hanya admin (selain itu 403). */
    public function kelola(User $user): bool
    {
        return $user->isAdmin();
    }
}

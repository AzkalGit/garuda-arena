<?php

namespace App\Policies;

use App\Models\Field;
use App\Models\User;

class FieldPolicy
{
    /** Melihat daftar dan menambah lapangan: hanya admin (selain itu 403). */
    public function kelola(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Mengubah data atau status satu lapangan: hanya admin (selain itu 403). */
    public function ubah(User $user, Field $field): bool
    {
        return $user->isAdmin();
    }
}

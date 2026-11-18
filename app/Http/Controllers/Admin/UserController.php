<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola', User::class);

        $cari = $request->query('q');
        $cari = is_string($cari) ? trim($cari) : '';

        $users = User::query()
            ->withCount('bookings')
            ->when($cari !== '', function ($query) use ($cari) {
                $pola = '%'.$cari.'%';

                $query->where(function ($sub) use ($pola) {
                    $sub->where('name', 'like', $pola)
                        ->orWhere('email', 'like', $pola);
                });
            })
            ->orderByRaw("case when role = 'admin' then 0 else 1 end")
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'cari' => $cari,
            'total' => User::count(),
        ]);
    }
}

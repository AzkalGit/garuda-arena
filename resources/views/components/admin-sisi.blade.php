@php
    $menu = [
        ['Dashboard', 'admin.dashboard', 'admin.dashboard'],
        ['Kelola Booking', 'admin.bookings.index', 'admin.bookings.*'],
        ['Kelola Lapangan', 'admin.fields.index', 'admin.fields.*'],
        ['Kelola User', 'admin.users.index', 'admin.users.*'],
        ['Laporan', 'admin.reports.index', 'admin.reports.*'],
    ];
@endphp

<div class="admin-sisi-isi">
    <a href="{{ route('home') }}" class="admin-merek">
        <span class="admin-merek-nama">Garuda Arena</span>
        <span class="admin-merek-sub">Panel Admin</span>
    </a>

    <nav class="admin-menu" aria-label="Menu admin">
        @foreach ($menu as [$label, $rute, $pola])
            @if (Route::has($rute))
                <a href="{{ route($rute) }}" @class(['admin-menu-link', 'aktif' => request()->routeIs($pola)])>{{ $label }}</a>
            @endif
        @endforeach
    </nav>

    <div class="admin-sisi-bawah">
        <a href="{{ route('home') }}" class="admin-menu-link">Lihat situs</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-menu-link admin-keluar">Keluar</button>
        </form>
    </div>
</div>
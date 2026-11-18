@php
    $bola = '<circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/>'
        . '<path d="M12 7.2l4.56 3.32-1.74 5.36H9.18l-1.74-5.36z"/>'
        . '<path d="M12 7.2V2.5M16.56 10.52l4.48-1.48M14.82 15.88l2.76 3.81M9.18 15.88l-2.76 3.81M7.44 10.52L2.96 9.04" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>';

    $menu = [
        ['Dashboard', 'admin.dashboard', 'admin.dashboard', '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>'],
        ['Kelola Lapangan', 'admin.fields.index', 'admin.fields.*', $bola],
        ['Kelola Booking', 'admin.bookings.index', 'admin.bookings.*', '<path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a3 3 0 0 1 3 3v2H2V7a3 3 0 0 1 3-3h1V3a1 1 0 0 1 1-1z"/><path fill-rule="evenodd" d="M2 11h20v7a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3zm5.5 4.5l3 3 6-6-1.4-1.4-4.6 4.6-1.6-1.6z"/>'],
        ['Kelola User', 'admin.users.index', 'admin.users.*', '<circle cx="12" cy="7.5" r="4.5"/><path d="M3 21a9 9 0 0 1 18 0z"/>'],
        ['Laporan', 'admin.reports.index', 'admin.reports.*', '<rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="3" width="4" height="18" rx="1"/><rect x="17" y="8" width="4" height="13" rx="1"/>'],
    ];
@endphp

<div class="admin-sisi-isi">
    <a href="{{ route('home') }}" class="admin-merek">
        <span class="admin-merek-logo">
            <svg class="admin-menu-ikon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $bola !!}</svg>
        </span>
        <span class="admin-merek-teks">
            <span class="admin-merek-nama">Garuda Arena</span>
            <span class="admin-merek-sub">Panel Admin</span>
        </span>
    </a>

    <nav class="admin-menu" aria-label="Menu admin">
        @foreach ($menu as [$label, $rute, $pola, $ikon])
            @if (Route::has($rute))
                <a href="{{ route($rute) }}" @class(['admin-menu-link', 'aktif' => request()->routeIs($pola)])>
                    <svg class="admin-menu-ikon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $ikon !!}</svg>
                    <span>{{ $label }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="admin-sisi-bawah">
        <a href="{{ route('home') }}" class="admin-menu-link">Lihat situs</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-menu-link admin-keluar">Keluar</button>
        </form>
        <p class="admin-catatan">Tugas kuliah PBW – Kelompok 6</p>
    </div>
</div>
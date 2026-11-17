@php
    $menu = [
        ['Dashboard', 'admin.dashboard', 'admin.dashboard', '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
        ['Kelola Booking', 'admin.bookings.index', 'admin.bookings.*', '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>'],
        ['Kelola Lapangan', 'admin.fields.index', 'admin.fields.*', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M12 5v14"/><circle cx="12" cy="12" r="2.5"/>'],
        ['Kelola User', 'admin.users.index', 'admin.users.*', '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Laporan', 'admin.reports.index', 'admin.reports.*', '<path d="M5 21V11M12 21V4M19 21v-7"/>'],
    ];
@endphp

<div class="admin-sisi-isi">
    <a href="{{ route('home') }}" class="admin-merek">
        <span class="admin-merek-logo">
            <svg class="admin-menu-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/><path d="M12 8l3.5 2.5-1.3 4h-4.4l-1.3-4z"/>
            </svg>
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
                    <svg class="admin-menu-ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon !!}</svg>
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
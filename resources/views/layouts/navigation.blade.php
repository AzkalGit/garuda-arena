@php
    $menu = [
        ['Beranda', route('home'), request()->routeIs('home')],
        ['Daftar Lapangan', route('lapangan.index'), request()->routeIs('lapangan.*')],
        ['Fasilitas', route('home').'#features', false],
        ['Galeri', route('home').'#gallery', false],
        ['Kontak', route('home').'#contact', false],
    ];
    if (auth()->check() && ! auth()->user()->isAdmin() && Route::has('riwayat.index')) {
        $menu[] = ['Riwayat Booking', route('riwayat.index'), request()->routeIs('riwayat.*')];
    }
    if (auth()->check() && auth()->user()->isAdmin() && Route::has('admin.bookings.index')) {
        $menu[] = ['Panel Admin', route('admin.bookings.index'), request()->routeIs('admin.*')];
    }
@endphp

<nav x-data="{ open: false }" class="navbar">
    <div class="wadah">
        <div class="navbar-baris">
            <div class="navbar-kiri">
                <a href="{{ route('home') }}" class="navbar-logo">
                    <x-application-logo class="logo-navbar" />
                    <span class="navbar-nama">Garuda Arena</span>
                </a>

                <div class="menu-desktop">
                    @foreach ($menu as [$label, $url, $aktif])
                        <a href="{{ $url }}" @class(['menu-link', 'aktif' => $aktif])>{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="navbar-kanan">
                @guest
                    <a href="{{ route('login') }}" class="tombol-masuk">Masuk</a>
                    <a href="{{ route('register') }}" class="tombol-daftar">Daftar</a>
                @endguest

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button data-peran="{{ auth()->user()->role }}" class="tombol-pengguna">
                                <span>{{ auth()->user()->name }}</span>
                                @if (auth()->user()->isAdmin())
                                    <span class="lencana-admin">Admin</span>
                                @endif
                                <svg class="ikon-panah" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <div class="navbar-hamburger">
                <button @click="open = ! open" aria-label="Buka menu" class="tombol-hamburger">
                    <svg class="ikon-hamburger" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak class="menu-mobile">
        <div class="menu-mobile-bagian">
            @foreach ($menu as [$label, $url, $aktif])
                <a href="{{ $url }}" @class(['menu-mobile-link', 'aktif' => $aktif])>{{ $label }}</a>
            @endforeach
        </div>

        <div class="menu-mobile-bagian">
            @guest
                <a href="{{ route('login') }}" class="menu-mobile-link warna-hijau">Masuk</a>
                <a href="{{ route('register') }}" class="menu-mobile-link warna-aksen">Daftar</a>
            @endguest

            @auth
                <div class="pengguna-info">
                    <div class="pengguna-nama">{{ auth()->user()->name }}</div>
                    <div class="pengguna-email">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}" class="menu-mobile-link">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="menu-mobile-link">Keluar</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
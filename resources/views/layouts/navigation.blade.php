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

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <x-application-logo class="block h-9 w-auto fill-current text-green-700" />
                    <span class="font-heading font-semibold text-green-900">Garuda Arena</span>
                </a>

                <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                    @foreach ($menu as [$label, $url, $aktif])
                        <a href="{{ $url }}" @class([
                            'transition-colors',
                            'text-green-700 font-semibold' => $aktif,
                            'text-gray-600 hover:text-green-700' => ! $aktif,
                        ])>{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-green-700 hover:text-green-900 px-3 py-2">Masuk</a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 px-5 py-2 rounded-full">Daftar</a>
                @endguest

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button data-peran="{{ auth()->user()->role }}" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition">
                                <span>{{ auth()->user()->name }}</span>
                                @if (auth()->user()->isAdmin())
                                    <span class="ms-2 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Admin</span>
                                @endif
                                <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
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

            <div class="flex items-center md:hidden">
                <button @click="open = ! open" aria-label="Buka menu" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden border-t border-gray-200">
        <div class="px-4 py-3 space-y-1">
            @foreach ($menu as [$label, $url, $aktif])
                <a href="{{ $url }}" @class([
                    'block px-3 py-2 rounded-lg text-base',
                    'text-green-700 font-semibold bg-green-50' => $aktif,
                    'text-gray-600 font-medium hover:bg-gray-50 hover:text-green-700' => ! $aktif,
                ])>{{ $label }}</a>
            @endforeach
        </div>

        <div class="px-4 py-3 border-t border-gray-200 space-y-1">
            @guest
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-green-700 hover:bg-gray-50">Masuk</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-orange-600 hover:bg-gray-50">Daftar</a>
            @endguest

            @auth
                <div class="px-3 pb-2">
                    <div class="font-medium text-base text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-gray-50">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-start px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-gray-50">Keluar</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
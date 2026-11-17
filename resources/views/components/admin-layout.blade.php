<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Panel Admin - {{ config('app.name', 'Garuda Arena') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/garuda.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">

        {{ $head ?? '' }}
    </head>
    <body>
        <div class="admin-halaman" x-data="{ buka: false }" @keydown.escape.window="buka = false">
            <aside class="admin-sisi">
                <x-admin-sisi />
            </aside>

            <div x-show="buka" x-cloak class="admin-menu-mobile">
                <aside class="admin-sisi-mobile">
                    <x-admin-sisi />
                </aside>
                <button type="button" class="admin-latar" @click="buka = false" aria-label="Tutup menu"></button>
            </div>

            <div class="admin-utama">
                <header class="admin-atas">
                    <button type="button" class="admin-tombol-menu" @click="buka = true" aria-label="Buka menu">
                        <svg class="admin-ikon-menu" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="admin-atas-judul">Panel Admin</span>
                    <span class="admin-atas-pengguna">
                        <span class="admin-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="admin-atas-nama">{{ auth()->user()->name }}</span>
                    </span>
                </header>

                <main class="admin-isi">
                    @isset($header)
                        <div class="admin-header">{{ $header }}</div>
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
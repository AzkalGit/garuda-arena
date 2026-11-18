@props(['judul' => 'Masuk'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $judul }} - {{ config('app.name', 'Garuda Arena') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/garuda.css') }}">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    </head>
    <body>
        <div class="auth-halaman">
            <main class="auth-kartu">
                <section class="auth-visual">
                    <a href="{{ route('home') }}" class="auth-logo" aria-label="Ke beranda Garuda Arena">
                        <x-application-logo class="auth-logo-ikon" />
                    </a>
                    <div>
                        <small class="auth-visual-kecil">Siap main?</small>
                        <h2 class="auth-visual-judul">Cari lapangan, pilih jam, dan booking dalam satu tempat.</h2>
                    </div>
                </section>

                <section class="auth-sisi-formulir">
                    <div class="auth-formulir">
                        {{ $slot }}
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
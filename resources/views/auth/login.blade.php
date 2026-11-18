<x-auth-layout judul="Masuk">
    <h1 class="auth-judul">Masuk ke akun</h1>
    <p class="auth-sub">Masuk untuk melihat jadwal dan mengelola booking lapangan futsalmu.</p>

    @if (session('status'))
        <div class="auth-info" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label class="auth-label" for="email">Email kamu</label>
        <input @class(['auth-input', 'galat' => $errors->has('email')]) type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@gmail.com" autocomplete="username" required autofocus>
        @error('email')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <label class="auth-label" for="password">Kata sandi</label>
        <input @class(['auth-input', 'galat' => $errors->has('password')]) type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
        @error('password')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <div class="auth-baris">
            <label class="auth-ingat" for="remember_me">
                <input type="checkbox" id="remember_me" name="remember">
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-lupa" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="auth-tombol">Masuk</button>

        <p class="auth-pindah">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </form>
</x-auth-layout>
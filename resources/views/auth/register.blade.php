<x-auth-layout judul="Daftar">
    <h1 class="auth-judul">Buat akun</h1>
    <p class="auth-sub">Cek jadwal, pilih jam, dan booking lapangan futsal dalam hitungan menit.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label class="auth-label" for="name">Nama lengkap</label>
        <input @class(['auth-input', 'galat' => $errors->has('name')]) type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama kamu" autocomplete="name" required autofocus>
        @error('name')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <label class="auth-label" for="email">Email kamu</label>
        <input @class(['auth-input', 'galat' => $errors->has('email')]) type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@gmail.com" autocomplete="username" required>
        @error('email')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <label class="auth-label" for="password">Kata sandi</label>
        <input @class(['auth-input', 'galat' => $errors->has('password')]) type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password" required>
        @error('password')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <label class="auth-label" for="password_confirmation">Konfirmasi kata sandi</label>
        <input @class(['auth-input', 'galat' => $errors->has('password_confirmation')]) type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" autocomplete="new-password" required>
        @error('password_confirmation')
            <p class="auth-galat-teks" role="alert">{{ $message }}</p>
        @enderror

        <button type="submit" class="auth-tombol">Daftar</button>

        <p class="auth-pindah">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </form>
</x-auth-layout>
<x-app-layout>
    <x-slot name="header">
        <nav class="flex text-sm font-sans text-gray-500 mb-2 space-x-2 overflow-x-auto whitespace-nowrap" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-green-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('lapangan.index') }}" class="hover:text-green-700">Daftar Lapangan</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $field->name }}</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">{{ $field->name }}</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Kolom kiri: detail --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-6 shadow-sm">
                    <div class="w-full aspect-[16/10] bg-green-50 rounded-2xl overflow-hidden">
                        <img src="{{ $field->imageUrl() }}" alt="Lapangan {{ $field->name }}" class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 bg-green-100 text-green-900 font-semibold text-xs rounded-full uppercase tracking-wider">{{ $field->typeLabel() }}</span>
                        <span class="text-sm font-semibold text-orange-600">★ {{ $field->ratingLabel() }}
                            <span class="font-normal text-gray-500">({{ $field->reviews_count }} ulasan)</span>
                        </span>
                    </div>
                    <p class="text-gray-500 text-sm sm:text-base">{{ $field->location }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-3">
                    <h2 class="font-heading font-bold text-xl text-gray-900">Tentang Lapangan</h2>
                    <p class="text-gray-500 leading-relaxed">{{ $field->description }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h2 class="font-heading font-bold text-xl text-gray-900">Spesifikasi Lapangan</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach ($spesifikasi as $item)
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                <div class="text-xs text-gray-500 font-medium">{{ $item['label'] }}</div>
                                <div class="font-heading font-bold text-sm text-gray-900 mt-1">{{ $item['nilai'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h2 class="font-heading font-bold text-xl text-gray-900">Fasilitas Tersedia</h2>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach ($fasilitas as $item)
                            <span class="bg-green-100 text-green-900 px-3.5 py-2 rounded-xl text-sm font-semibold">✓ {{ $item }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: kartu harga dan booking --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-6 space-y-5 lg:sticky lg:top-28">
                    <div class="flex items-baseline justify-between border-b border-gray-200 pb-4">
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tarif Sewa</span>
                            <div class="font-heading font-extrabold text-2xl text-green-700">{{ $field->priceLabel() }}</div>
                        </div>
                        <span class="text-sm font-semibold text-gray-500">/ jam</span>
                    </div>

                    @if (session('booking_sukses'))
                        <div role="status" class="rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-900 space-y-1">
                            <div class="font-semibold">Booking berhasil dibuat</div>
                            <div>Kode: <span class="font-bold">{{ session('booking_sukses.kode') }}</span></div>
                            <div>{{ session('booking_sukses.tanggal') }}, {{ session('booking_sukses.jam') }}</div>
                            <div>Total: {{ session('booking_sukses.total') }}</div>
                            <div class="text-green-700">Selesaikan pembayaran sebelum batas waktu. Halaman pembayaran menyusul.</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                            <ul class="list-disc ms-4 space-y-1">
                                @foreach ($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Pilih tanggal (GET, tanpa JavaScript) --}}
                    <form method="GET" action="{{ route('lapangan.show', $field) }}" class="space-y-2">
                        <label for="tanggal" class="block text-sm font-medium text-gray-900">Tanggal</label>
                        <div class="flex gap-2">
                            <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" min="{{ $hariIni }}"
                                   class="flex-1 min-w-0 rounded-xl border-gray-200 text-sm focus:border-green-700 focus:ring-green-700">
                            <button type="submit" class="px-4 py-2 rounded-xl border border-green-700 text-green-700 text-sm font-semibold hover:bg-green-50">Cek</button>
                        </div>
                        <p class="text-xs text-gray-500">{{ $tanggalLabel }}</p>
                    </form>

                    {{-- Booking (POST) --}}
                    <form method="POST" action="{{ route('booking.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="field_id" value="{{ $field->id }}">
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                        <fieldset class="space-y-2">
                            <legend class="text-sm font-medium text-gray-900">Jam mulai</legend>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($slot as $s)
                                    <label class="block">
                                        <input type="radio" name="start_hour" value="{{ $s['jam'] }}" class="peer sr-only"
                                               @disabled(! $s['tersedia'] || $mode !== 'pelanggan')
                                               @checked((int) old('start_hour') === $s['jam'])>
                                        <span class="block text-center rounded-lg border border-gray-200 px-2 py-2 text-sm font-medium text-gray-900 cursor-pointer
                                                     peer-checked:bg-green-700 peer-checked:text-white peer-checked:border-green-700
                                                     peer-focus-visible:ring-2 peer-focus-visible:ring-green-700
                                                     peer-disabled:bg-gray-100 peer-disabled:text-gray-400 peer-disabled:cursor-not-allowed">
                                            {{ $s['label'] }}
                                            @if ($s['keterangan'])
                                                <span class="block text-[10px] font-normal">{{ $s['keterangan'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="space-y-2">
                            <legend class="text-sm font-medium text-gray-900">Durasi</legend>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($durasiOpsi as $d)
                                    <label class="block">
                                        <input type="radio" name="duration" value="{{ $d }}" class="peer sr-only"
                                               @disabled($mode !== 'pelanggan')
                                               @checked((int) old('duration', 1) === $d)>
                                        <span class="block text-center rounded-lg border border-gray-200 px-2 py-2 text-sm font-medium text-gray-900 cursor-pointer
                                                     peer-checked:bg-green-700 peer-checked:text-white peer-checked:border-green-700
                                                     peer-focus-visible:ring-2 peer-focus-visible:ring-green-700
                                                     peer-disabled:bg-gray-100 peer-disabled:text-gray-400 peer-disabled:cursor-not-allowed">
                                            {{ $d }} jam
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500">Total = tarif per jam × durasi. Durasi yang menabrak jam terisi atau melewati {{ sprintf('%02d:00', config('garuda.close_hour')) }} akan ditolak.</p>
                        </fieldset>

                        @if ($mode === 'pelanggan')
                            <button type="submit" class="block w-full text-center bg-orange-600 hover:bg-orange-700 text-white py-3.5 px-4 rounded-xl font-heading font-bold text-base">
                                Booking Sekarang
                            </button>
                        @elseif ($mode === 'tamu')
                            <a href="{{ route('login') }}" class="block w-full text-center bg-orange-600 hover:bg-orange-700 text-white py-3.5 px-4 rounded-xl font-heading font-bold text-base">
                                Masuk untuk Booking
                            </a>
                        @else
                            <p class="text-sm text-gray-500 text-center">Akun admin tidak dapat membuat booking.</p>
                        @endif
                    </form>

                    <p class="text-xs text-gray-500 text-center leading-relaxed">
                        Pembayaran lewat transfer manual. Slot ditahan 1 jam setelah booking.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
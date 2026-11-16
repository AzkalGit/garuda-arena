<x-app-layout>
    <section class="bg-gradient-to-br from-green-800 to-green-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 grid gap-10 lg:grid-cols-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <h1 class="font-heading text-4xl sm:text-5xl font-bold leading-tight">
                    Booking Lapangan Futsal, <span class="text-green-200">Tanpa Ribet</span>
                </h1>
                <p class="text-green-100 text-base sm:text-lg max-w-2xl leading-relaxed">
                    Pilih lapangan, tanggal, dan jam. Bayar dengan unggah bukti transfer, lalu admin memverifikasi pesananmu.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('lapangan.index') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold px-7 py-3 rounded-2xl shadow-lg transition-colors">Pilih Lapangan</a>
                    <a href="#features" class="border border-white/30 hover:bg-white/10 text-white font-semibold px-7 py-3 rounded-2xl transition-colors">Lihat Keunggulan</a>
                </div>
                <dl class="grid grid-cols-3 gap-4 pt-6 border-t border-white/20 max-w-lg">
                    <div>
                        <dt class="text-xs text-green-200">Lapangan aktif</dt>
                        <dd class="text-2xl font-bold">{{ $jumlahAktif }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-green-200">Jam operasional</dt>
                        <dd class="text-2xl font-bold">{{ $jamBuka }}–{{ $jamTutup }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-green-200">Batas bayar</dt>
                        <dd class="text-2xl font-bold">{{ $batasBayar }} menit</dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-5">
                <img src="{{ asset('images/1-hero-gedung-malam.jpg') }}" alt="Gedung Garuda Arena di malam hari" class="rounded-3xl shadow-2xl w-full h-64 sm:h-80 object-cover">
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-green-700 font-semibold text-sm uppercase tracking-wider">Pilihan Lapangan</span>
                <h2 class="font-heading text-3xl font-bold text-gray-900">Lapangan Favorit</h2>
            </div>
            <a href="{{ route('lapangan.index') }}" class="text-green-700 hover:text-green-900 font-semibold text-sm">Lihat semua lapangan →</a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($unggulan as $field)
                <x-field-card :field="$field" />
            @endforeach
        </div>
    </section>

    <section id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-green-700 font-semibold text-sm uppercase tracking-wider">Kenapa Memilih Kami</span>
            <h2 class="font-heading text-3xl font-bold text-gray-900">Keunggulan Garuda Arena</h2>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($fasilitas as $item)
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <h3 class="font-heading font-semibold text-gray-900 mb-2">{{ $item['judul'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $item['teks'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="gallery" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="mb-8">
            <span class="text-green-700 font-semibold text-sm uppercase tracking-wider">Galeri Arena</span>
            <h2 class="font-heading text-3xl font-bold text-gray-900">Suasana di Garuda Arena</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($galeri as $foto)
                <div class="overflow-hidden rounded-2xl h-48 sm:h-64">
                    <img src="{{ asset($foto['file']) }}" alt="{{ $foto['alt'] }}" loading="lazy" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
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

            {{-- Kolom kanan: kartu harga --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-6 space-y-5 lg:sticky lg:top-28">
                    <div class="flex items-baseline justify-between border-b border-gray-200 pb-4">
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tarif Sewa</span>
                            <div class="font-heading font-extrabold text-2xl text-green-700">{{ $field->priceLabel() }}</div>
                        </div>
                        <span class="text-sm font-semibold text-gray-500">/ jam</span>
                    </div>

                    <p class="text-sm text-gray-500 leading-relaxed">
                        Pemilihan tanggal, jam, dan durasi sewa akan tersedia segera.
                    </p>

                    <span aria-disabled="true" class="block w-full text-center bg-orange-600 text-white py-3.5 px-4 rounded-xl font-heading font-bold text-base opacity-50 cursor-not-allowed">
                        Booking Sekarang
                    </span>

                    <p class="text-xs text-gray-500 text-center leading-relaxed">
                        Pembayaran lewat transfer manual. Slot ditahan 1 jam setelah booking.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
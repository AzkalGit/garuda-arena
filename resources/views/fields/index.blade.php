<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Lapangan</h1>
        <p class="mt-1 text-sm font-sans text-gray-500">Temukan lapangan futsal berkualitas dengan fasilitas lengkap dan harga terjangkau.</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" action="{{ route('lapangan.index') }}" class="bg-white border border-gray-200 rounded-2xl p-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
            <div>
                <label for="jenis" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Jenis Lantai</label>
                <select id="jenis" name="jenis" class="w-full rounded-xl border-gray-200 text-sm focus:border-green-700 focus:ring-green-700">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($jenis === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="harga" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Rentang Harga</label>
                <select id="harga" name="harga" class="w-full rounded-xl border-gray-200 text-sm focus:border-green-700 focus:ring-green-700">
                    <option value="">Semua Harga</option>
                    @foreach ($hargaOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($harga === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="urut" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Urutkan</label>
                <select id="urut" name="urut" class="w-full rounded-xl border-gray-200 text-sm focus:border-green-700 focus:ring-green-700">
                    @foreach ($urutOpsi as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($urut === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors">Terapkan</button>
                <a href="{{ route('lapangan.index') }}" class="flex-1 text-center border border-gray-200 text-gray-600 hover:text-green-700 hover:border-green-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors">Reset</a>
            </div>
        </form>

        <p class="mt-6 mb-4 text-sm font-semibold text-gray-500">Menampilkan {{ $fields->count() }} dari {{ $total }} lapangan</p>

        @if ($fields->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center max-w-md mx-auto my-8">
                <h3 class="font-heading font-bold text-xl text-gray-900 mb-2">Tidak ada lapangan yang cocok</h3>
                <p class="text-sm text-gray-500 mb-6">Coba ubah filter untuk menemukan lapangan yang tersedia.</p>
                <a href="{{ route('lapangan.index') }}" class="inline-block bg-green-700 hover:bg-green-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">Reset Filter</a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($fields as $field)
                    <x-field-card :field="$field" />
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
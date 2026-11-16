@props(['field'])

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden flex flex-col shadow-sm">
    <div class="relative aspect-[4/3] bg-green-50 overflow-hidden">
        <img src="{{ $field->imageUrl() }}" alt="Lapangan {{ $field->name }}" loading="lazy" class="w-full h-full object-cover">
        <span class="absolute top-3 left-3 bg-green-700 text-white text-xs font-bold px-3 py-1 rounded-lg shadow">{{ $field->typeLabel() }}</span>
        <span class="absolute bottom-3 right-3 bg-black/60 text-white text-xs font-semibold px-2.5 py-1 rounded-lg">★ {{ $field->ratingLabel() }} ({{ $field->reviews_count }} ulasan)</span>
    </div>

    <div class="p-5 space-y-3 flex-1">
        <p class="text-xs font-medium text-gray-500">{{ $field->location }}</p>
        <h3 class="font-heading font-semibold text-lg text-gray-900">{{ $field->name }}</h3>
        <div class="flex flex-wrap gap-1.5">
            @foreach (collect($field->facilities)->take(3) as $fasilitas)
                <span class="bg-green-50 text-green-800 text-xs font-medium px-2.5 py-1 rounded-lg">{{ $fasilitas }}</span>
            @endforeach
            @if ($field->isActive())
                <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-lg">Tersedia</span>
            @else
                <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-lg">Perawatan</span>
            @endif
        </div>
    </div>

    <div class="px-5 pb-5 pt-3 border-t border-gray-200 flex items-center justify-between gap-3">
        <div>
            <span class="block text-[11px] uppercase tracking-wider font-semibold text-gray-500">Mulai dari</span>
            <span class="font-heading font-bold text-green-700">{{ $field->priceLabel() }} <span class="text-xs font-normal text-gray-500">/ jam</span></span>
        </div>

        @if ($field->isActive())
            <a href="{{ url('/lapangan/'.$field->id) }}" class="shrink-0 bg-green-700 hover:bg-green-800 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors">Lihat Detail</a>
        @else
            <span aria-disabled="true" class="shrink-0 bg-gray-200 text-gray-500 font-semibold text-sm px-4 py-2.5 rounded-xl cursor-not-allowed">Tidak Tersedia</span>
        @endif
    </div>
</div>
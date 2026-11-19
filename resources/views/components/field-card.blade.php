@props(['field'])

<div class="kartu-lapangan">
    <div class="kartu-foto">
        <img src="{{ $field->imageUrl() }}" alt="Lapangan {{ $field->name }}" loading="lazy">
        <span class="kartu-jenis">{{ $field->typeLabel() }}</span>
        <span class="kartu-rating">★ {{ $field->ratingLabel() }} ({{ $field->reviews_count }} ulasan)</span>
    </div>

    <div class="kartu-isi">
        <p class="kartu-lokasi">{{ $field->location }}</p>
        <h3 class="kartu-nama">{{ $field->name }}</h3>
        <div class="kartu-fasilitas">
            @foreach (collect($field->facilities)->take(3) as $fasilitas)
            <span class="lencana-fasilitas"><x-ikon-fasilitas :nama="$fasilitas" /> {{ $fasilitas }}</span>
            @endforeach
            @if ($field->isActive())
                <span class="lencana-tersedia">Tersedia</span>
            @else
                <span class="lencana-perawatan">Perawatan</span>
            @endif
        </div>
    </div>

    <div class="kartu-kaki">
        <div>
            <span class="kartu-harga-label">Mulai dari</span>
            <span class="kartu-harga">{{ $field->priceLabel() }} <span class="kartu-satuan">/ jam</span></span>
        </div>

        @if ($field->isActive())
            <a href="{{ route('lapangan.show', $field) }}" class="tombol-hijau tombol-detail">Lihat Detail</a>
        @else
            <span aria-disabled="true" class="tombol-nonaktif">Tidak Tersedia</span>
        @endif
    </div>
</div>
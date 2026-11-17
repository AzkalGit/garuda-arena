<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-lapangan.css') }}">
    </x-slot>

    <div class="riwayat-halaman">
        <div class="riwayat-kepala">
            <h1 class="riwayat-judul">{{ $judul }}</h1>
            <p class="riwayat-keterangan">Isi data lapangan, lalu simpan. Rating dan ulasan tidak diubah dari sini.</p>
        </div>

        @if ($errors->any())
            <div class="riwayat-galat">Data belum bisa disimpan. Periksa isian yang bertanda galat di bawah.</div>
        @endif

        <form method="POST" action="{{ $aksi }}" enctype="multipart/form-data" class="form-lapangan">
            @csrf
            @if ($metode === 'PUT')
                @method('PUT')
            @endif

            <div class="form-kisi">
                <div class="form-baris form-penuh">
                    <label for="name" class="form-label">Nama lapangan</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $field->name) }}" maxlength="100" required class="form-isian">
                    @error('name')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris">
                    <label for="type" class="form-label">Jenis lantai</label>
                    <select id="type" name="type" class="form-isian">
                        @foreach ($jenisOpsi as $kunci => $label)
                            <option value="{{ $kunci }}" @selected(old('type', $field->type) === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris">
                    <label for="price_per_hour" class="form-label">Tarif per jam (Rp)</label>
                    <input id="price_per_hour" name="price_per_hour" type="number" min="10000" max="5000000" step="1000" value="{{ old('price_per_hour', $field->price_per_hour) }}" required class="form-isian">
                    @error('price_per_hour')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris form-penuh">
                    <label for="location" class="form-label">Lokasi</label>
                    <input id="location" name="location" type="text" value="{{ old('location', $field->location) }}" maxlength="150" required class="form-isian">
                    @error('location')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris">
                    <label for="size" class="form-label">Ukuran</label>
                    <input id="size" name="size" type="text" value="{{ old('size', $field->size) }}" placeholder="25 x 15 m" maxlength="30" required class="form-isian">
                    @error('size')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris">
                    <label for="capacity" class="form-label">Kapasitas</label>
                    <input id="capacity" name="capacity" type="text" value="{{ old('capacity', $field->capacity) }}" placeholder="10 pemain" maxlength="30" required class="form-isian">
                    @error('capacity')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris form-penuh">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-isian">
                        <option value="aktif" @selected(old('status', $field->status) === 'aktif')>Aktif (bisa dipesan)</option>
                        <option value="nonaktif" @selected(old('status', $field->status) === 'nonaktif')>Nonaktif (tidak bisa dipesan)</option>
                    </select>
                    @error('status')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris form-penuh">
                    <label for="facilities" class="form-label">Fasilitas</label>
                    <input id="facilities" name="facilities" type="text" value="{{ old('facilities', $field->fasilitasTeks()) }}" placeholder="Parkir Luas, Musala, Shower Air Panas" maxlength="500" class="form-isian">
                    <div class="form-bantuan">Pisahkan dengan koma. Maksimal 12 fasilitas.</div>
                    @error('facilities')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris form-penuh">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000" required class="form-isian">{{ old('description', $field->description) }}</textarea>
                    @error('description')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>

                <div class="form-baris form-penuh">
                    <label for="image" class="form-label">Foto lapangan</label>
                    @if ($field->exists)
                        <img src="{{ $field->imageUrl() }}" alt="Foto saat ini" class="form-pratinjau">
                    @endif
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png" class="form-isian">
                    <div class="form-bantuan">JPG atau PNG, maksimal 2 MB. Kosongkan bila tidak ingin mengganti foto.</div>
                    @error('image')<div class="isian-galat">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-aksi">
                <a href="{{ route('admin.fields.index') }}" class="tombol-batal">Batal</a>
                <button type="submit" class="tombol-simpan">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>
<?php

namespace App\Http\Requests;

use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', Field::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('fields', 'name')->ignore($this->route('field'))],
            'type' => ['required', Rule::in(array_keys(Field::TYPE_LABELS))],
            'price_per_hour' => ['required', 'integer', 'min:10000', 'max:5000000'],
            'location' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'size' => ['required', 'string', 'max:30'],
            'capacity' => ['required', 'string', 'max:30'],
            'facilities' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in([Field::STATUS_AKTIF, Field::STATUS_NONAKTIF])],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lapangan wajib diisi.',
            'name.max' => 'Nama lapangan maksimal 100 karakter.',
            'name.unique' => 'Nama lapangan sudah dipakai.',
            'type.required' => 'Jenis lantai wajib dipilih.',
            'type.in' => 'Jenis lantai tidak dikenal.',
            'price_per_hour.required' => 'Tarif per jam wajib diisi.',
            'price_per_hour.integer' => 'Tarif harus berupa angka bulat (rupiah).',
            'price_per_hour.min' => 'Tarif minimal Rp10.000 per jam.',
            'price_per_hour.max' => 'Tarif maksimal Rp5.000.000 per jam.',
            'location.required' => 'Lokasi wajib diisi.',
            'location.max' => 'Lokasi maksimal 150 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'size.required' => 'Ukuran wajib diisi.',
            'size.max' => 'Ukuran maksimal 30 karakter.',
            'capacity.required' => 'Kapasitas wajib diisi.',
            'capacity.max' => 'Kapasitas maksimal 30 karakter.',
            'facilities.max' => 'Daftar fasilitas maksimal 500 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak dikenal.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG atau PNG.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
            'image.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }

    /**
     * Data siap simpan (tanpa foto). Fasilitas dari teks berkoma menjadi daftar:
     * dirapikan, tanpa duplikat, maksimal 12 butir @ 40 karakter.
     *
     * @return array<string, mixed>
     */
    public function dataLapangan(): array
    {
        $data = $this->safe()->except(['image', 'facilities']);

        $data['facilities'] = collect(explode(',', (string) $this->validated('facilities')))
            ->map(fn ($butir) => mb_substr(trim($butir), 0, 40))
            ->filter(fn ($butir) => $butir !== '')
            ->unique()
            ->take(12)
            ->values()
            ->all();

        return $data;
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SubmitProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('pay', $this->route('booking'));

        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'proof.required' => 'Pilih foto bukti transfer terlebih dahulu.',
            'proof.file' => 'Bukti pembayaran harus berupa berkas.',
            'proof.uploaded' => 'Berkas gagal diunggah (kemungkinan lebih dari 2 MB).',
            'proof.mimes' => 'Bukti pembayaran harus berupa gambar JPG atau PNG.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ];
    }
}
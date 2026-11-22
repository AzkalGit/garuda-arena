<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreReviewRequest extends FormRequest
{
    /** Hanya pemilik booking; orang lain 404 (lewat BookingPolicy::review). */
    public function authorize(): bool
    {
        Gate::authorize('review', $this->route('booking'));

        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rating.required' => 'Pilih jumlah bintang dulu.',
            'rating.integer' => 'Rating harus berupa angka 1 sampai 5.',
            'rating.between' => 'Rating harus berupa angka 1 sampai 5.',
            'comment.required' => 'Tulis ulasanmu dulu.',
            'comment.min' => 'Ulasan minimal 5 karakter.',
            'comment.max' => 'Ulasan maksimal 500 karakter.',
        ];
    }
}

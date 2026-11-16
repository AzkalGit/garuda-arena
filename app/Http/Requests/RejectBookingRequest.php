<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RejectBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('verify', $this->route('booking'));
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.min' => 'Alasan penolakan minimal 5 karakter.',
            'alasan.max' => 'Alasan penolakan maksimal 255 karakter.',
        ];
    }
}

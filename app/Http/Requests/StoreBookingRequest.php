<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Booking::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $jamPertama = (int) config('garuda.open_hour');
        $jamTerakhir = (int) config('garuda.close_hour') - 1;

        return [
            'field_id' => [
                'required', 'integer',
                Rule::exists('fields', 'id')->where('status', Field::STATUS_AKTIF),
            ],
            'tanggal' => [
                'bail', 'required', 'date_format:Y-m-d',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Carbon::createFromFormat('!Y-m-d', $value)->lt(Carbon::today())) {
                        $fail('Tanggal booking tidak boleh sebelum hari ini.');
                    }
                },
            ],
            'start_hour' => ['required', 'integer', "between:{$jamPertama},{$jamTerakhir}"],
            'duration' => ['required', 'integer', 'between:1,3'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'field_id.required' => 'Lapangan belum dipilih.',
            'field_id.exists' => 'Lapangan ini tidak tersedia untuk dipesan.',
            'tanggal.required' => 'Tanggal belum dipilih.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'start_hour.required' => 'Jam mulai belum dipilih.',
            'start_hour.integer' => 'Jam mulai tidak valid.',
            'start_hour.between' => 'Jam mulai di luar jam operasional.',
            'duration.required' => 'Durasi belum dipilih.',
            'duration.integer' => 'Durasi tidak valid.',
            'duration.between' => 'Durasi harus 1 sampai 3 jam.',
        ];
    }
}

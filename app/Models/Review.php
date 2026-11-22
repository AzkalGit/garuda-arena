<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable(['user_id', 'field_id', 'booking_id', 'rating', 'comment'])]
class Review extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Bintang untuk tampilan, mis. rating 4 = "★★★★☆". */
    public function bintangLabel(): string
    {
        return str_repeat('★', $this->rating).str_repeat('☆', 5 - $this->rating);
    }

    /** Tanggal ulasan, mis. "20 November 2026". */
    public function tanggalLabel(): string
    {
        return $this->created_at->locale('id')->translatedFormat('j F Y');
    }

    /**
     * Simpan ulasan dan perbarui rating lapangan dalam satu transaksi.
     * Rating baru = rata-rata berbobot dari rating dan jumlah ulasan yang sudah ada.
     */
    public static function buat(User $user, Booking $booking, int $rating, string $komentar): self
    {
        return DB::transaction(function () use ($user, $booking, $rating, $komentar) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->user_id !== $user->id || ! $booking->bisaDiulas()) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini tidak dapat diulas.',
                ]);
            }

            $field = Field::query()->lockForUpdate()->findOrFail($booking->field_id);

            $ulasan = self::create([
                'user_id' => $user->id,
                'field_id' => $field->id,
                'booking_id' => $booking->id,
                'rating' => $rating,
                'comment' => $komentar,
            ]);

            $jumlahLama = (int) $field->reviews_count;
            $totalBintang = ((float) $field->rating * $jumlahLama) + $rating;

            $field->update([
                'reviews_count' => $jumlahLama + 1,
                'rating' => round($totalBintang / ($jumlahLama + 1), 1),
            ]);

            return $ulasan;
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code', 'user_id', 'field_id', 'date', 'start_hour', 'duration',
    'price_per_hour', 'total', 'status', 'expires_at', 'proof_path',
    'proof_name', 'proof_submitted_at', 'rejection_reason',
    'cancelled_reason', 'verified_by', 'verified_at',
])]
class Booking extends Model
{
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';

    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

    public const STATUS_DIKONFIRMASI = 'dikonfirmasi';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_SELESAI = 'selesai';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_hour' => 'integer',
            'duration' => 'integer',
            'price_per_hour' => 'integer',
            'total' => 'integer',
            'expires_at' => 'datetime',
            'proof_submitted_at' => 'datetime',
            'verified_at' => 'datetime',
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

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Jam selesai = jam mulai + durasi. */
    public function endHour(): int
    {
        return $this->start_hour + $this->duration;
    }
}

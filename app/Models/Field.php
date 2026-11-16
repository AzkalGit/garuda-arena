<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'type', 'price_per_hour', 'location', 'description', 'size',
    'capacity', 'facilities', 'image', 'rating', 'reviews_count', 'status',
])]
class Field extends Model
{
    public const TYPE_INTERLOCK = 'interlock';

    public const TYPE_RUMPUT = 'rumput';

    public const TYPE_VINYL = 'vinyl';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_NONAKTIF = 'nonaktif';

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'price_per_hour' => 'integer',
            'rating' => 'decimal:1',
            'reviews_count' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }
}

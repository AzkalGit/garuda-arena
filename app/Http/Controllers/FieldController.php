<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FieldController extends Controller
{
    private const HARGA = [
        'under150' => 'Di bawah Rp150.000',
        '150to200' => 'Rp150.000 – Rp200.000',
        'above200' => 'Di atas Rp200.000',
    ];

    private const URUT = [
        'recommended' => 'Rekomendasi',
        'priceAsc' => 'Harga Termurah',
        'priceDesc' => 'Harga Tertinggi',
        'ratingDesc' => 'Rating Tertinggi',
    ];

    public function index(Request $request): View
    {
        $jenis = $this->pilihan($request->query('jenis'), Field::TYPE_LABELS);
        $harga = $this->pilihan($request->query('harga'), self::HARGA);
        $urut = $this->pilihan($request->query('urut'), self::URUT) ?? 'recommended';

        $query = Field::query();

        if ($jenis !== null) {
            $query->where('type', $jenis);
        }

        match ($harga) {
            'under150' => $query->where('price_per_hour', '<', 150000),
            '150to200' => $query->whereBetween('price_per_hour', [150000, 200000]),
            'above200' => $query->where('price_per_hour', '>', 200000),
            default => null,
        };

        match ($urut) {
            'priceAsc' => $query->orderBy('price_per_hour'),
            'priceDesc' => $query->orderByDesc('price_per_hour'),
            'ratingDesc' => $query->orderByDesc('rating'),
            default => null,
        };

        return view('fields.index', [
            'fields' => $query->orderBy('id')->get(),
            'total' => Field::count(),
            'jenis' => $jenis,
            'harga' => $harga,
            'urut' => $urut,
            'jenisOpsi' => Field::TYPE_LABELS,
            'hargaOpsi' => self::HARGA,
            'urutOpsi' => self::URUT,
        ]);
    }

    /** Nilai query hanya dipakai bila berupa teks dan ada di daftar pilihan. */
    private function pilihan(mixed $nilai, array $opsi): ?string
    {
        return is_string($nilai) && array_key_exists($nilai, $opsi) ? $nilai : null;
    }
}

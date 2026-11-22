<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

    public function show(Request $request, Field $field): View
    {
        abort_unless($field->isActive(), 404);

        Booking::perbaruiStatus();

        $jamOperasional = sprintf(
            '%02d:00 – %02d:00',
            config('garuda.open_hour'),
            config('garuda.close_hour')
        );

        $tanggal = $this->tanggalDipilih($request->query('tanggal'));
        $pengguna = $request->user();

        // tamu | pelanggan | dilarang (admin tidak boleh membuat booking)
        $mode = $pengguna === null
            ? 'tamu'
            : ($pengguna->can('create', Booking::class) ? 'pelanggan' : 'dilarang');

        return view('fields.show', [
            'field' => $field,
            'fasilitas' => collect($field->facilities)->all(),
            'spesifikasi' => [
                ['label' => 'Jenis Lantai', 'nilai' => $field->typeLabel()],
                ['label' => 'Ukuran', 'nilai' => $field->size],
                ['label' => 'Kapasitas', 'nilai' => $field->capacity],
                ['label' => 'Jam Operasional', 'nilai' => $jamOperasional],
            ],
            'mode' => $mode,
            'tanggal' => $tanggal->toDateString(),
            'tanggalLabel' => $tanggal->locale('id')->translatedFormat('l, j F Y'),
            'hariIni' => Carbon::today()->toDateString(),
            'slot' => Booking::petaJam($field, $tanggal),
            'durasiOpsi' => [1, 2, 3],
            'ulasan' => $field->reviews()
                ->with('user')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
        ]);
    }

    /** Nilai query hanya dipakai bila berupa teks dan ada di daftar pilihan. */
    private function pilihan(mixed $nilai, array $opsi): ?string
    {
        return is_string($nilai) && array_key_exists($nilai, $opsi) ? $nilai : null;
    }

    /** Tanggal dari ?tanggal=Y-m-d; tidak valid atau sudah lewat -> hari ini. */
    private function tanggalDipilih(mixed $nilai): Carbon
    {
        $hariIni = Carbon::today();

        if (! is_string($nilai) || ! Carbon::canBeCreatedFromFormat($nilai, 'Y-m-d')) {
            return $hariIni;
        }

        $tanggal = Carbon::createFromFormat('!Y-m-d', $nilai);

        if ($tanggal->format('Y-m-d') !== $nilai || $tanggal->lt($hariIni)) {
            return $hariIni;
        }

        return $tanggal;
    }
}

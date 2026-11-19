<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $nilai = $request->query('jenis');
        $jenis = is_string($nilai) && array_key_exists($nilai, Field::TYPE_LABELS) ? $nilai : null;

        $query = Field::query()->where('status', Field::STATUS_AKTIF);

        if ($jenis !== null) {
            $query->where('type', $jenis);
        }

        $daftar = $query->orderBy('id')->get()->map(fn (Field $field) => [
            'nama' => $field->name,
            'lokasi' => $field->location,
            'jenis' => $field->typeLabel(),
            'foto' => $field->imageUrl(),
        ])->all();

        $tab = [['label' => 'Semua', 'url' => route('galeri.index'), 'aktif' => $jenis === null]];

        foreach (Field::TYPE_LABELS as $kunci => $label) {
            $tab[] = [
                'label' => $label,
                'url' => route('galeri.index', ['jenis' => $kunci]),
                'aktif' => $jenis === $kunci,
            ];
        }

        return view('galeri.index', [
            'daftar' => $daftar,
            'tab' => $tab,
        ]);
    }
}

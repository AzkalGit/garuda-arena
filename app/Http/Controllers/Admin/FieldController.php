<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FieldRequest;
use App\Models\Field;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FieldController extends Controller
{
    private const GAMBAR_BAWAAN = 'images/2-venue-interlock.jpg';

    public function index(Request $request): View
    {
        Gate::authorize('kelola', Field::class);

        $status = $request->query('status');
        if (! in_array($status, [Field::STATUS_AKTIF, Field::STATUS_NONAKTIF], true)) {
            $status = null;
        }

        $cari = $request->query('q');
        $cari = is_string($cari) ? trim($cari) : '';

        $fields = Field::query()
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($cari !== '', function ($query) use ($cari) {
                $pola = '%'.$cari.'%';

                $query->where(fn ($sub) => $sub->where('name', 'like', $pola)->orWhere('location', 'like', $pola));
            })
            ->orderBy('id')
            ->get();

        $jumlah = Field::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tab = [[
            'label' => 'Semua',
            'jumlah' => (int) $jumlah->sum(),
            'url' => route('admin.fields.index', array_filter(['q' => $cari])),
            'aktif' => $status === null,
        ]];

        foreach ([Field::STATUS_AKTIF => 'Aktif', Field::STATUS_NONAKTIF => 'Nonaktif'] as $kunci => $label) {
            $tab[] = [
                'label' => $label,
                'jumlah' => (int) ($jumlah[$kunci] ?? 0),
                'url' => route('admin.fields.index', array_filter(['status' => $kunci, 'q' => $cari])),
                'aktif' => $status === $kunci,
            ];
        }

        return view('admin.fields.index', [
            'fields' => $fields,
            'tab' => $tab,
            'status' => $status,
            'cari' => $cari,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('kelola', Field::class);

        return view('admin.fields.form', [
            'field' => new Field([
                'type' => Field::TYPE_INTERLOCK,
                'price_per_hour' => 150000,
                'status' => Field::STATUS_AKTIF,
            ]),
            'judul' => 'Tambah Lapangan',
            'aksi' => route('admin.fields.store'),
            'metode' => 'POST',
            'jenisOpsi' => Field::TYPE_LABELS,
        ]);
    }

    public function store(FieldRequest $request): RedirectResponse
    {
        $data = $request->dataLapangan();
        $data['rating'] = 0;
        $data['reviews_count'] = 0;
        $data['image'] = $request->hasFile('image')
            ? $this->simpanGambar($request->file('image'))
            : self::GAMBAR_BAWAAN;

        $field = Field::create($data);

        return redirect()
            ->route('admin.fields.index')
            ->with('admin_pesan', 'Lapangan '.$field->name.' ditambahkan.');
    }

    public function edit(Field $field): View
    {
        Gate::authorize('ubah', $field);

        return view('admin.fields.form', [
            'field' => $field,
            'judul' => 'Ubah Lapangan',
            'aksi' => route('admin.fields.update', ['field' => $field]),
            'metode' => 'PUT',
            'jenisOpsi' => Field::TYPE_LABELS,
        ]);
    }

    public function update(FieldRequest $request, Field $field): RedirectResponse
    {
        $data = $request->dataLapangan();
        $gambarLama = $field->gambarUnggahan();

        if ($request->hasFile('image')) {
            $data['image'] = $this->simpanGambar($request->file('image'));
        }

        $field->update($data);

        if ($request->hasFile('image') && $gambarLama !== null) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.fields.index')
            ->with('admin_pesan', 'Data lapangan '.$field->name.' diperbarui.');
    }

    /** Aktif <-> nonaktif. Booking yang sudah ada tidak disentuh. */
    public function status(Field $field): RedirectResponse
    {
        Gate::authorize('ubah', $field);

        $field->update([
            'status' => $field->isActive() ? Field::STATUS_NONAKTIF : Field::STATUS_AKTIF,
        ]);

        return redirect()
            ->route('admin.fields.index')
            ->with('admin_pesan', 'Lapangan '.$field->name.' sekarang '.$field->statusLabel().'.');
    }

    /** Simpan ke disk publik; yang dicatat adalah path relatif terhadap public/. */
    private function simpanGambar(UploadedFile $berkas): string
    {
        return 'storage/'.$berkas->store('lapangan', 'public');
    }
}

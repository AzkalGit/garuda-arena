<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    /** Contoh: "19:00 – 21:00". */
    public function jamLabel(): string
    {
        return sprintf('%02d:00 – %02d:00', $this->start_hour, $this->endHour());
    }

    public function totalLabel(): string
    {
        return 'Rp '.number_format($this->total, 0, ',', '.');
    }

    /** Contoh: "Sabtu, 21 November 2026". */
    public function tanggalLabel(): string
    {
        return $this->date->locale('id')->translatedFormat('l, j F Y');
    }

    /** Contoh: "Rp 180.000 / jam". */
    public function tarifLabel(): string
    {
        return 'Rp '.number_format($this->price_per_hour, 0, ',', '.').' / jam';
    }

    /** Masih bisa dibayar: menunggu_pembayaran dan belum lewat batas bayar. */
    public function bisaDibayar(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_PEMBAYARAN
            && ($this->expires_at === null || $this->expires_at->gte(now()));
    }

    /** Sisa waktu bayar dalam menit (dibulatkan ke atas, minimal 0). */
    public function sisaMenit(): int
    {
        if ($this->expires_at === null) {
            return 0;
        }

        return max(0, (int) ceil(($this->expires_at->timestamp - now()->timestamp) / 60));
    }

    /**
     * Simpan bukti bayar (disk privat) dan ubah status ke menunggu_verifikasi.
     * Status dicek ulang di dalam transaksi dengan kunci baris.
     *
     * @throws ValidationException
     */
    public function kirimBukti(UploadedFile $berkas): void
    {
        self::perbaruiStatus();

        DB::transaction(function () use ($berkas) {
            $baris = self::query()->lockForUpdate()->findOrFail($this->id);

            if (! $baris->bisaDibayar()) {
                throw ValidationException::withMessages([
                    'proof' => 'Booking ini sudah tidak dapat dibayar (waktu habis, dibatalkan, atau bukti sudah dikirim).',
                ]);
            }

            $path = $berkas->store('bukti-bayar', 'local');

            $baris->update([
                'proof_path' => $path,
                'proof_name' => $berkas->getClientOriginalName(),
                'proof_submitted_at' => now(),
                'status' => self::STATUS_MENUNGGU_VERIFIKASI,
                'expires_at' => null,
                'rejection_reason' => null,
            ]);
        });

        $this->refresh();
    }

    /**
     * Buat booking baru berstatus menunggu_pembayaran.
     * Semua pengecekan (jam, bentrok) dilakukan di dalam transaksi dengan
     * kunci baris lapangan, supaya dua permintaan bersamaan tidak sama-sama lolos.
     *
     * @throws ValidationException
     */
    public static function createPending(User $user, Field $field, Carbon $tanggal, int $jamMulai, int $durasi): self
    {
        return DB::transaction(function () use ($user, $field, $tanggal, $jamMulai, $durasi) {
            $lapangan = Field::query()->lockForUpdate()->find($field->id);

            if ($lapangan === null || ! $lapangan->isActive()) {
                throw ValidationException::withMessages([
                    'field_id' => 'Lapangan ini tidak tersedia untuk dipesan.',
                ]);
            }

            $selesai = $jamMulai + $durasi;

            if ($jamMulai < (int) config('garuda.open_hour') || $selesai > (int) config('garuda.close_hour')) {
                throw ValidationException::withMessages([
                    'start_hour' => 'Durasi melewati jam operasional (maksimal sampai '
                        .sprintf('%02d:00', config('garuda.close_hour')).').',
                ]);
            }

            if ($tanggal->copy()->startOfDay()->setTime($jamMulai, 0)->lt(now())) {
                throw ValidationException::withMessages([
                    'start_hour' => 'Jam yang dipilih sudah lewat.',
                ]);
            }

            self::perbaruiStatus();

            $bentrok = self::queryAktif($lapangan->id, $tanggal)
                ->where('start_hour', '<', $selesai)
                ->whereRaw('start_hour + duration > ?', [$jamMulai])
                ->exists();

            if ($bentrok) {
                throw ValidationException::withMessages([
                    'start_hour' => 'Jam tersebut bentrok dengan booking lain. Pilih jam atau durasi yang berbeda.',
                ]);
            }

            return self::create([
                'code' => self::buatKode($tanggal),
                'user_id' => $user->id,
                'field_id' => $lapangan->id,
                'date' => $tanggal->toDateString(),
                'start_hour' => $jamMulai,
                'duration' => $durasi,
                'price_per_hour' => $lapangan->price_per_hour,
                'total' => $lapangan->price_per_hour * $durasi,
                'status' => self::STATUS_MENUNGGU_PEMBAYARAN,
                'expires_at' => now()->addMinutes((int) config('garuda.pay_window_minutes')),
            ]);
        });
    }

    /** @return array<string, string> kunci status => label tampilan, urut sesuai tab filter riwayat. */
    public static function daftarStatus(): array
    {
        return [
            self::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu pembayaran',
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu verifikasi',
            self::STATUS_DIKONFIRMASI => 'Dikonfirmasi',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
        ];
    }

    public function statusLabel(): string
    {
        return self::daftarStatus()[$this->status] ?? $this->status;
    }

    /** Akhiran kelas CSS lencana: `menunggu_pembayaran` -> `menunggu-pembayaran`. */
    public function statusKelas(): string
    {
        return str_replace('_', '-', $this->status);
    }

    /**
     * Pelanggan membatalkan booking. Status dicek ulang di dalam transaksi
     * (dengan kunci baris); hanya `menunggu_pembayaran` yang boleh dibatalkan.
     */
    public function batalkan(): void
    {
        DB::transaction(function () {
            $baris = self::query()->lockForUpdate()->findOrFail($this->id);

            if ($baris->status !== self::STATUS_MENUNGGU_PEMBAYARAN) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini tidak bisa dibatalkan lagi.',
                ]);
            }

            $baris->forceFill([
                'status' => self::STATUS_DIBATALKAN,
                'expires_at' => null,
                'cancelled_reason' => 'Dibatalkan pelanggan',
            ])->save();
        });

        $this->refresh();
    }

    /**
     * Pembaruan status otomatis (tanpa scheduler): dipanggil controller/model
     * sebelum membaca data.
     * - batas bayar terlewat -> dibatalkan
     * - dikonfirmasi yang jam mainnya sudah lewat -> selesai
     */
    public static function perbaruiStatus(): void
    {
        self::query()
            ->where('status', self::STATUS_MENUNGGU_PEMBAYARAN)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => self::STATUS_DIBATALKAN,
                'cancelled_reason' => 'Waktu pembayaran habis',
            ]);

        $sekarang = now();

        $idSelesai = self::query()
            ->where('status', self::STATUS_DIKONFIRMASI)
            ->whereDate('date', '<=', $sekarang->toDateString())
            ->get()
            ->filter(fn (self $booking) => Carbon::parse($booking->date)
                ->startOfDay()
                ->setTime($booking->endHour(), 0)
                ->lte($sekarang))
            ->pluck('id');

        if ($idSelesai->isNotEmpty()) {
            self::query()
                ->whereIn('id', $idSelesai)
                ->update(['status' => self::STATUS_SELESAI]);
        }
    }

    /**
     * Peta jam mulai (open_hour .. close_hour-1) untuk satu lapangan dan tanggal.
     *
     * @return array<int, array{jam:int, label:string, tersedia:bool, keterangan:?string}>
     */
    public static function petaJam(Field $field, Carbon $tanggal): array
    {
        $terisi = self::jamTerisi($field->id, $tanggal);
        $sekarang = now();
        $peta = [];

        for ($jam = (int) config('garuda.open_hour'); $jam < (int) config('garuda.close_hour'); $jam++) {
            $lewat = $tanggal->copy()->startOfDay()->setTime($jam, 0)->lt($sekarang);
            $isi = in_array($jam, $terisi, true);

            $peta[] = [
                'jam' => $jam,
                'label' => sprintf('%02d:00', $jam),
                'tersedia' => ! $lewat && ! $isi,
                'keterangan' => $isi ? 'Terisi' : ($lewat ? 'Lewat' : null),
            ];
        }

        return $peta;
    }

    /** Admin hanya bisa memverifikasi booking yang menunggu verifikasi. */
    public function bisaDiverifikasi(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_VERIFIKASI;
    }

    /**
     * Admin menyetujui bukti bayar -> dikonfirmasi.
     * Status dicek ulang di dalam transaksi dengan kunci baris.
     *
     * @throws ValidationException
     */
    public function setujui(User $admin): void
    {
        DB::transaction(function () use ($admin) {
            $baris = self::query()->lockForUpdate()->findOrFail($this->id);

            if (! $baris->bisaDiverifikasi()) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini tidak sedang menunggu verifikasi.',
                ]);
            }

            $baris->forceFill([
                'status' => self::STATUS_DIKONFIRMASI,
                'rejection_reason' => null,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ])->save();
        });

        $this->refresh();
    }

    /**
     * Admin menolak bukti bayar. Jam mulai belum lewat -> kembali ke
     * menunggu_pembayaran dengan batas bayar baru; sudah lewat -> dibatalkan.
     * Alasan penolakan selalu disimpan; berkas bukti lama dihapus.
     *
     * @throws ValidationException
     */
    public function tolak(User $admin, string $alasan): void
    {
        $berkasLama = null;

        DB::transaction(function () use ($admin, $alasan, &$berkasLama) {
            $baris = self::query()->lockForUpdate()->findOrFail($this->id);

            if (! $baris->bisaDiverifikasi()) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini tidak sedang menunggu verifikasi.',
                ]);
            }

            $berkasLama = $baris->proof_path;
            $mulai = $baris->date->copy()->startOfDay()->setTime($baris->start_hour, 0);
            $masihBisa = $mulai->gt(now());

            $baris->forceFill([
                'status' => $masihBisa ? self::STATUS_MENUNGGU_PEMBAYARAN : self::STATUS_DIBATALKAN,
                'expires_at' => $masihBisa ? now()->addMinutes((int) config('garuda.pay_window_minutes')) : null,
                'rejection_reason' => $alasan,
                'cancelled_reason' => $masihBisa ? null : 'Bukti pembayaran ditolak: '.$alasan,
                'proof_path' => null,
                'proof_name' => null,
                'proof_submitted_at' => null,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ])->save();
        });

        if ($berkasLama !== null) {
            Storage::disk('local')->delete($berkasLama);
        }

        $this->refresh();
    }

    /** @return array<int, int> jam-jam (mulai) yang sudah terpakai booking tidak dibatalkan. */
    private static function jamTerisi(int $fieldId, Carbon $tanggal): array
    {
        $jam = [];

        foreach (self::queryAktif($fieldId, $tanggal)->get(['start_hour', 'duration']) as $booking) {
            foreach (range($booking->start_hour, $booking->endHour() - 1) as $j) {
                $jam[] = $j;
            }
        }

        return array_values(array_unique($jam));
    }

    /** Booking pada lapangan dan tanggal tertentu yang belum dibatalkan. */
    private static function queryAktif(int $fieldId, Carbon $tanggal): Builder
    {
        return self::query()
            ->where('field_id', $fieldId)
            ->whereDate('date', $tanggal->toDateString())
            ->where('status', '!=', self::STATUS_DIBATALKAN);
    }

    /** Kode unik format GA-ddmm-XXXX (ddmm = tanggal main). */
    private static function buatKode(Carbon $tanggal): string
    {
        do {
            $kode = 'GA-'.$tanggal->format('dm').'-'.Str::upper(Str::random(4));
        } while (self::query()->where('code', $kode)->exists());

        return $kode;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->has('lapangan')) {
            $tujuan = $this->tujuanCekCepat($request);
            if ($tujuan) {
                return $tujuan;
            }
        }

        $unggulan = Field::where('status', Field::STATUS_AKTIF)
            ->orderBy('id')
            ->take(3)
            ->get();

        return view('home', [
            'unggulan' => $unggulan,
            'daftarLapangan' => Field::where('status', Field::STATUS_AKTIF)->orderBy('id')->get(),
            'hariIni' => Carbon::today()->toDateString(),
            'jumlahAktif' => Field::where('status', Field::STATUS_AKTIF)->count(),
            'jamBuka' => sprintf('%02d.00', (int) config('garuda.open_hour')),
            'jamTutup' => sprintf('%02d.00', (int) config('garuda.close_hour')),
            'batasBayar' => (int) config('garuda.pay_window_minutes'),
            'fasilitas' => [
                ['judul' => 'Cek Jadwal Langsung', 'teks' => 'Pilih tanggal, jam, dan durasi. Sistem memeriksa bentrok jadwal otomatis.'],
                ['judul' => 'Bayar Lewat Transfer', 'teks' => 'Unggah bukti transfer, lalu admin memverifikasi pesananmu.'],
                ['judul' => 'Tiga Jenis Lantai', 'teks' => 'Interlock, rumput sintetis, dan vinyl sesuai gaya mainmu.'],
                ['judul' => 'Fasilitas Lengkap', 'teks' => 'Parkir luas, kantin, ruang ganti, dan pencahayaan malam.'],
            ],
            'galeri' => [
                ['file' => 'images/5-galeri-kantin.jpg', 'alt' => 'Kantin Garuda Arena'],
                ['file' => 'images/6-galeri-ruang-ganti.jpg', 'alt' => 'Ruang ganti'],
                ['file' => 'images/7-galeri-pertandingan.jpg', 'alt' => 'Suasana pertandingan'],
                ['file' => 'images/8-galeri-lapangan-vip.jpg', 'alt' => 'Lapangan utama'],
            ],
        ]);
    }

    /**
     * Kartu "Cek Ketersediaan Cepat" (GET ke beranda): alihkan ke detail
     * lapangan aktif (+ tanggal) atau ke daftar lapangan. Nilai tak dikenal
     * diabaikan dan beranda tampil biasa.
     */
    private function tujuanCekCepat(Request $request): ?RedirectResponse
    {
        $pilihan = $request->query('lapangan');

        if (! is_string($pilihan)) {
            return null;
        }

        if ($pilihan === 'semua') {
            return redirect()->route('lapangan.index');
        }

        if (! ctype_digit($pilihan)) {
            return null;
        }

        $field = Field::where('status', Field::STATUS_AKTIF)->find((int) $pilihan);
        if (! $field) {
            return null;
        }

        $parameter = ['field' => $field];
        $tanggal = $request->query('tanggal');
        if (is_string($tanggal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $parameter['tanggal'] = $tanggal;
        }

        return redirect()->route('lapangan.show', $parameter);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $unggulan = Field::where('status', Field::STATUS_AKTIF)
            ->orderBy('id')
            ->take(3)
            ->get();

        return view('home', [
            'unggulan' => $unggulan,
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
}

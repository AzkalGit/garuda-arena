<x-app-layout>
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/pembayaran.css') }}">
    </x-slot>

    <div class="pembayaran-halaman">
        <div class="langkah-daftar">
            <div class="langkah langkah-selesai">
                <span class="langkah-angka">✓</span>
                Pilih jadwal
            </div>
            <div class="langkah langkah-sekarang">
                <span class="langkah-angka">2</span>
                Pembayaran
            </div>
            <div class="langkah {{ $selesaiBayar ? 'langkah-selesai' : '' }}">
                <span class="langkah-angka">3</span>
                Selesai
            </div>
        </div>

        @error('proof')
            <div class="pembayaran-galat">{{ $message }}</div>
        @enderror

        <div class="pembayaran-grid">
            <div class="pembayaran-utama">
                @if ($tahap === 'bayar')
                    <section class="kartu-bayar">
                        <div class="kartu-bayar-kepala">
                            <h1 class="judul-bayar">Transfer manual</h1>
                            <span class="lencana-waktu">Bayar dalam {{ $sisaMenit }} menit</span>
                        </div>
                        <p class="catatan-bayar">
                            Transfer sesuai total ke rekening berikut, lalu unggah bukti transfernya. Slot ditahan 1 jam setelah booking.
                        </p>
                        <div class="rekening">
                            <div class="rekening-bank">Bank Garuda (fiktif)</div>
                            <div class="rekening-nomor">1234 5678 9012</div>
                            <div class="rekening-nama">a.n. Garuda Arena Futsal</div>
                        </div>
                        <div class="jumlah-transfer">
                            <span class="jumlah-label">Jumlah transfer</span>
                            <span class="jumlah-nilai">{{ $booking->totalLabel() }}</span>
                        </div>
                    </section>

                    <section class="kartu-bayar">
                        <div>
                            <h2 class="judul-bayar">Unggah bukti pembayaran</h2>
                            <p class="catatan-bayar">JPG atau PNG, maksimal 2 MB.</p>
                        </div>
                        <form method="POST" action="{{ route('pembayaran.store', ['booking' => $booking->code]) }}" enctype="multipart/form-data" class="form-bukti">
                            @csrf
                            <input type="file" name="proof" accept="image/jpeg,image/png" required class="input-berkas">
                            <div class="form-bukti-aksi">
                                <button type="submit" class="tombol-kirim">Kirim Bukti Pembayaran</button>
                            </div>
                        </form>
                    </section>
                @elseif ($tahap === 'verifikasi')
                    <section class="kotak-status">
                        <h1 class="kotak-status-judul">Bukti pembayaran terkirim</h1>
                        <p class="kotak-status-isi">Bukti pembayaran sudah dikirim. Booking menunggu verifikasi admin.</p>
                        @if ($booking->proof_name)
                            <p class="kotak-status-berkas">Berkas: {{ $booking->proof_name }}</p>
                        @endif
                    </section>
                @elseif ($tahap === 'dikonfirmasi')
                    <section class="kotak-status">
                        <h1 class="kotak-status-judul">Booking dikonfirmasi</h1>
                        <p class="kotak-status-isi">Pembayaran sudah diverifikasi. Sampai jumpa di lapangan!</p>
                    </section>
                @else
                    <section class="kotak-status kotak-status-gagal">
                        <h1 class="kotak-status-judul">Booking tidak dapat diproses</h1>
                        <p class="kotak-status-isi">{{ $booking->cancelled_reason ?? 'Booking ini sudah dibatalkan.' }}</p>
                        <a href="{{ route('lapangan.index') }}" class="tombol-kembali">Kembali ke Daftar Lapangan</a>
                    </section>
                @endif
            </div>

            <aside class="ringkasan">
                <h2 class="ringkasan-judul">Ringkasan booking</h2>
                <img src="{{ $booking->field->imageUrl() }}" alt="{{ $booking->field->name }}" class="ringkasan-foto">
                <dl class="ringkasan-daftar">
                    <div class="ringkasan-baris"><dt>Lapangan</dt><dd>{{ $booking->field->name }}</dd></div>
                    <div class="ringkasan-baris"><dt>Tanggal</dt><dd>{{ $booking->tanggalLabel() }}</dd></div>
                    <div class="ringkasan-baris"><dt>Jam</dt><dd>{{ $booking->jamLabel() }}</dd></div>
                    <div class="ringkasan-baris"><dt>Durasi</dt><dd>{{ $booking->duration }} jam</dd></div>
                    <div class="ringkasan-baris"><dt>Tarif</dt><dd>{{ $booking->tarifLabel() }}</dd></div>
                </dl>
                <div class="ringkasan-total">
                    <span class="ringkasan-total-label">Total</span>
                    <span class="ringkasan-total-nilai">{{ $booking->totalLabel() }}</span>
                </div>
                <div class="ringkasan-kode">
                    Kode booking: <strong>{{ $booking->code }}</strong>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center gap-6 overflow-x-auto pb-1 text-sm font-semibold">
            <div class="flex shrink-0 items-center gap-2 text-green-700">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-700 text-white">✓</span>
                Pilih jadwal
            </div>
            <div class="flex shrink-0 items-center gap-2 text-gray-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-700 text-white">2</span>
                Pembayaran
            </div>
            <div class="flex shrink-0 items-center gap-2 {{ $selesaiBayar ? 'text-green-700' : 'text-gray-500' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $selesaiBayar ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-500' }}">3</span>
                Selesai
            </div>
        </div>

        @error('proof')
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if ($tahap === 'bayar')
                    <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <h1 class="font-heading text-xl font-bold">Transfer manual</h1>
                            <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                Bayar dalam {{ $sisaMenit }} menit
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            Transfer sesuai total ke rekening berikut, lalu unggah bukti transfernya. Slot ditahan 1 jam setelah booking.
                        </p>
                        <div class="rounded-xl bg-green-100 p-5">
                            <div class="text-xs font-medium text-green-700">Bank Garuda (fiktif)</div>
                            <div class="font-heading text-2xl font-extrabold text-green-900">1234 5678 9012</div>
                            <div class="text-sm text-green-900">a.n. Garuda Arena Futsal</div>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <span class="text-sm text-gray-500">Jumlah transfer</span>
                            <span class="font-heading text-xl font-extrabold text-green-700">{{ $booking->totalLabel() }}</span>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div>
                            <h2 class="font-heading text-xl font-bold">Unggah bukti pembayaran</h2>
                            <p class="mt-1 text-sm text-gray-500">JPG atau PNG, maksimal 2 MB.</p>
                        </div>
                        <form method="POST" action="{{ route('pembayaran.store', ['booking' => $booking->code]) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="file" name="proof" accept="image/jpeg,image/png" required
                                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-green-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-900">
                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-orange-700">
                                    Kirim Bukti Pembayaran
                                </button>
                            </div>
                        </form>
                    </section>
                @elseif ($tahap === 'verifikasi')
                    <section class="rounded-2xl border border-green-200 bg-green-50 p-6 text-sm text-green-800">
                        <h1 class="font-heading text-lg font-bold">Bukti pembayaran terkirim</h1>
                        <p class="mt-1">Bukti pembayaran sudah dikirim. Booking menunggu verifikasi admin.</p>
                        @if ($booking->proof_name)
                            <p class="mt-2 text-xs text-green-700">Berkas: {{ $booking->proof_name }}</p>
                        @endif
                    </section>
                @elseif ($tahap === 'dikonfirmasi')
                    <section class="rounded-2xl border border-green-200 bg-green-50 p-6 text-sm text-green-800">
                        <h1 class="font-heading text-lg font-bold">Booking dikonfirmasi</h1>
                        <p class="mt-1">Pembayaran sudah diverifikasi. Sampai jumpa di lapangan!</p>
                    </section>
                @else
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-800">
                        <h1 class="font-heading text-lg font-bold">Booking tidak dapat diproses</h1>
                        <p class="mt-1">{{ $booking->cancelled_reason ?? 'Booking ini sudah dibatalkan.' }}</p>
                        <a href="{{ route('lapangan.index') }}" class="mt-4 inline-flex rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">
                            Kembali ke Daftar Lapangan
                        </a>
                    </section>
                @endif
            </div>

            <aside class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                <h2 class="font-heading text-lg font-bold">Ringkasan booking</h2>
                <img src="{{ $booking->field->imageUrl() }}" alt="{{ $booking->field->name }}" class="aspect-[16/9] w-full rounded-xl bg-green-100 object-cover">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4 text-gray-500"><dt>Lapangan</dt><dd class="text-right font-medium text-gray-900">{{ $booking->field->name }}</dd></div>
                    <div class="flex justify-between gap-4 text-gray-500"><dt>Tanggal</dt><dd class="text-right font-medium text-gray-900">{{ $booking->tanggalLabel() }}</dd></div>
                    <div class="flex justify-between gap-4 text-gray-500"><dt>Jam</dt><dd class="text-right font-medium text-gray-900">{{ $booking->jamLabel() }}</dd></div>
                    <div class="flex justify-between gap-4 text-gray-500"><dt>Durasi</dt><dd class="text-right font-medium text-gray-900">{{ $booking->duration }} jam</dd></div>
                    <div class="flex justify-between gap-4 text-gray-500"><dt>Tarif</dt><dd class="text-right font-medium text-gray-900">{{ $booking->tarifLabel() }}</dd></div>
                </dl>
                <div class="flex items-center justify-between border-t border-gray-200 pt-3">
                    <span class="font-bold">Total</span>
                    <span class="font-heading text-lg font-extrabold text-green-700">{{ $booking->totalLabel() }}</span>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-500">
                    Kode booking: <strong class="text-gray-900">{{ $booking->code }}</strong>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
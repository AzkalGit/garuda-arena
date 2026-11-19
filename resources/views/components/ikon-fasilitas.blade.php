@props(['nama' => ''])

@php
    $teks = ' '.mb_strtolower((string) $nama).' ';

    $kataKunci = [
        'wifi' => ['wifi', 'wi-fi'],
        'parkir' => ['parkir'],
        'toilet' => ['toilet', ' wc ', 'kamar mandi'],
        'shower' => ['shower'],
        'kantin' => ['kantin', 'cafe', 'kafe'],
        'musala' => ['musala', 'mushola'],
        'ac' => [' ac '],
        'locker' => ['locker', 'loker'],
        'streaming' => ['streaming'],
        'tribun' => ['tribun'],
        'skor' => ['skor'],
        'cahaya' => ['pencahayaan', 'lampu'],
    ];

    $kunci = 'centang';
    foreach ($kataKunci as $ikon => $daftar) {
        foreach ($daftar as $kata) {
            if (str_contains($teks, $kata)) {
                $kunci = $ikon;
                break 2;
            }
        }
    }

    $bentuk = [
        'wifi' => '<path d="M2 9a15 15 0 0 1 20 0"/><path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5 5 0 0 1 7 0"/><path d="M12 19.5h.01"/>',
        'parkir' => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M10 16V8h3a2.5 2.5 0 0 1 0 5h-3"/>',
        'toilet' => '<circle cx="12" cy="5" r="2"/><path d="M12 9v6"/><path d="M8 11h8"/><path d="M9 21l3-6 3 6"/>',
        'shower' => '<path d="M4 11a8 8 0 0 1 16 0z"/><path d="M8 15v2"/><path d="M12 15v4"/><path d="M16 15v2"/>',
        'kantin' => '<path d="M5 8h11v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M8 3v2"/><path d="M12 3v2"/>',
        'musala' => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        'ac' => '<path d="M12 3v18"/><path d="M4.2 7.5l15.6 9"/><path d="M4.2 16.5l15.6-9"/>',
        'locker' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M12 3v18"/><path d="M9.5 12h.01"/><path d="M14.5 12h.01"/>',
        'streaming' => '<rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l6-3v10l-6-3z"/>',
        'tribun' => '<path d="M3 20h18"/><path d="M3 20v-4h6v-4h6V8h6v12"/>',
        'skor' => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M12 5v12"/><path d="M12 17v4"/><path d="M8 21h8"/>',
        'cahaya' => '<path d="M9 18h6"/><path d="M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.5 1 2.5h6c0-1 .3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
        'centang' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    ];
@endphp
<svg class="ikon-fasilitas" data-ikon="{{ $kunci }}" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $bentuk[$kunci] !!}</svg>
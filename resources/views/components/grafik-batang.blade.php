@props(['data' => [], 'judul' => 'Grafik batang'])

@php
    $lebar = 420;
    $tinggi = 220;
    $atas = 26;
    $bawah = 28;
    $areaTinggi = $tinggi - $atas - $bawah;
    $dasar = $atas + $areaTinggi;
    $lebarSlot = $lebar / max(1, count($data));
    $lebarBatang = round($lebarSlot * 0.6, 1);
    $maks = max(1, (int) (collect($data)->max('nilai') ?? 0));

    $batang = [];
    foreach (array_values($data) as $urut => $item) {
        $tinggiBatang = round(($item['nilai'] / $maks) * $areaTinggi, 1);
        $x = round($urut * $lebarSlot + ($lebarSlot - $lebarBatang) / 2, 1);
        $batang[] = [
            'x' => $x,
            'y' => $dasar - $tinggiBatang,
            'tinggi' => $tinggiBatang,
            'tengah' => round($x + $lebarBatang / 2, 1),
            'nilai' => $item['nilai'],
            'label' => $item['label'],
        ];
    }
@endphp

<svg class="grafik-svg" viewBox="0 0 {{ $lebar }} {{ $tinggi }}" role="img" aria-label="{{ $judul }}">
    <line class="grafik-garis" x1="0" y1="{{ $dasar }}" x2="{{ $lebar }}" y2="{{ $dasar }}" />

    @foreach ($batang as $b)
        <rect class="grafik-batang" x="{{ $b['x'] }}" y="{{ $b['y'] }}" width="{{ $lebarBatang }}" height="{{ $b['tinggi'] }}" rx="4" />
        <text class="grafik-nilai" x="{{ $b['tengah'] }}" y="{{ $b['y'] - 6 }}">{{ $b['nilai'] }}</text>
        <text class="grafik-label" x="{{ $b['tengah'] }}" y="{{ $tinggi - 8 }}">{{ $b['label'] }}</text>
    @endforeach
</svg>
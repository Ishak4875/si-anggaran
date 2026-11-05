@extends('layout.v_layout')
@section('title', 'Rekap Progres')
@section('content')
@php
    // Format sama dengan tabel "Progres per PPK & Peringkat" di dashboard.
    $rankBadge = function ($r) {
        if ($r === null) return '<span class="text-secondary">–</span>';
        $cls = $r == 1 ? 'text-bg-success' : ($r == 2 ? 'text-bg-primary' : ($r == 3 ? 'text-bg-info' : 'text-bg-light border'));
        return '<span class="badge ' . $cls . '">' . $r . '</span>';
    };
    $pct = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') . '%' : '–';
    $rb = fn ($v) => number_format($v / 1000, 0, ',', '.'); // ke ribuan (Rp.000)
    // Cell diwarnai merah jika nilainya di bawah acuan Ditjen SDA.
    $cellCls = fn ($v, $acuan) => ($v !== null && $acuan !== null && $v < $acuan) ? 'table-danger' : '';
@endphp
<div class="container-fluid">
    <style>
        .tabel-rekap { font-size: 13pt; }
        .tabel-rekap th,
        .tabel-rekap td { vertical-align: middle !important; }
        /* Angka (Pagu, Realisasi, Keu, Fis) rata kanan. */
        .tabel-rekap tbody td:nth-child(n+3):nth-child(-n+6) { text-align: right; }
    </style>

    @foreach ($jenis as $kode => $j)
        <div class="card mb-4">
            <div class="card-header position-relative">
                <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3 btn-unduh-rekap"
                        data-capture="capture-{{ $kode }}" data-file="progres-paket-{{ $kode }}.png">
                    <i class="bi bi-camera"></i> Unduh Gambar
                </button>
                <h3 class="card-title mb-0"><i class="bi bi-trophy me-1"></i> Progres {{ $j['judul'] }}</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" id="capture-{{ $kode }}">
                    <table class="table table-bordered mb-0 text-nowrap tabel-rekap" style="min-width:820px">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width:44px">No.</th>
                                <th>Satker / PPK</th>
                                <th>Pagu<br><small>(Rp.000)</small></th>
                                <th>Realisasi<br><small>(Rp.000)</small></th>
                                <th style="width:90px">Keu (%)</th>
                                <th style="width:90px">Fis (%)</th>
                                <th style="width:70px">Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris acuan: Ditjen SDA (diubah dari dashboard) --}}
                            <tr class="table-primary fw-bold">
                                <td>–</td>
                                <td>Ditjen SDA</td>
                                <td>–</td>
                                <td>–</td>
                                <td>{{ $pct($ditjen['keu']) }}</td>
                                <td>{{ $pct($ditjen['fis']) }}</td>
                                <td>–</td>
                            </tr>
                            {{-- Total jenis paket ini; badge membedakan gambar Reguler dan Inpres --}}
                            <tr class="table-secondary fw-bold">
                                <td>–</td>
                                <td>BWS Sulawesi IV Kendari <span class="badge text-bg-secondary">{{ $j['label'] }}</span></td>
                                <td>{{ $rb($j['total']['pagu']) }}</td>
                                <td>{{ $rb($j['total']['realisasi']) }}</td>
                                <td class="{{ $cellCls($j['total']['keu'], $ditjen['keu']) }}">{{ $pct($j['total']['keu']) }}</td>
                                <td class="{{ $cellCls($j['total']['fis'], $ditjen['fis']) }}">{{ $pct($j['total']['fis']) }}</td>
                                <td>–</td>
                            </tr>
                            @forelse ($j['satkers'] as $si => $s)
                                {{-- Baris satker --}}
                                <tr class="table-success fw-bold">
                                    <td>{{ $si + 1 }}</td>
                                    <td>{{ $s['nama'] }} <span class="badge text-bg-secondary">{{ $s['singkatan'] }}</span></td>
                                    <td>{{ $rb($s['pagu']) }}</td>
                                    <td>{{ $rb($s['realisasi']) }}</td>
                                    <td class="{{ $cellCls($s['keu'], $ditjen['keu']) }}">{{ $pct($s['keu']) }}</td>
                                    <td class="{{ $cellCls($s['fis'], $ditjen['fis']) }}">{{ $pct($s['fis']) }}</td>
                                    <td>{!! $rankBadge($s['rank']) !!}</td>
                                </tr>
                                {{-- Baris PPK --}}
                                @foreach ($s['ppks'] as $pi => $p)
                                    <tr>
                                        <td class="text-secondary">{{ chr(97 + $pi) }}</td>
                                        <td>
                                            {{ $p['ppk'] }}
                                            @if (!empty($p['pejabat']))
                                                <span class="text-secondary small">— {{ $p['pejabat'] }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $rb($p['pagu']) }}</td>
                                        <td>{{ $rb($p['realisasi']) }}</td>
                                        <td class="{{ $cellCls($p['keu'], $ditjen['keu']) }}">{{ $pct($p['keu']) }}</td>
                                        <td class="{{ $cellCls($p['fis'], $ditjen['fis']) }}">{{ $pct($p['fis']) }}</td>
                                        <td>{!! $rankBadge($p['rank']) !!}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-secondary">Belum ada data paket.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-center fw-bold border-top py-2 bg-white position-relative">
                        Status : {{ $statusJam['tanggal'] }} ; {{ $statusJam['jam'] }} WIB
                        {{-- Watermark; berada di dalam area capture agar ikut di gambar "Unduh Gambar" --}}
                        <span class="position-absolute bottom-0 end-0 me-2 mb-1 fw-normal fst-italic"
                              style="font-size: 11pt; color: #9a948a;">Created by Ishak; KPISDA</span>
                    </div>
                </div>
            </div>
            <div class="card-footer text-secondary small">
                Peringkat (Rank) dihitung berdasarkan <strong>Progres Keuangan</strong> tertinggi di antara {{ Str::lower($j['judul']) }} saja; nilai yang sama mendapat peringkat yang sama. Cell berwarna merah menandakan progres di bawah acuan Ditjen SDA.
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    document.querySelectorAll('.btn-unduh-rekap').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const area = document.getElementById(tombol.dataset.capture);
            // Lebar penuh tabel: di HP pembungkus .table-responsive lebih sempit dari tabel,
            // jadi kloningannya dilebarkan agar seluruh kolom ikut terpotret.
            const fullWidth = area.scrollWidth;

            tombol.disabled = true;
            html2canvas(area, {
                scale: 2,
                backgroundColor: '#ffffff',
                windowWidth: Math.max(window.innerWidth, fullWidth),
                onclone: function (clonedDoc) {
                    const wrap = clonedDoc.getElementById(tombol.dataset.capture);
                    wrap.style.overflow = 'visible';
                    wrap.style.width = fullWidth + 'px';
                    wrap.style.maxWidth = 'none';
                },
            }).then(function (canvas) {
                // Blob + object URL, bukan data URL: data URL gambar besar sering gagal diunduh di HP.
                return new Promise(function (resolve) {
                    canvas.toBlob(function (blob) {
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.download = tombol.dataset.file;
                        link.href = url;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
                        resolve();
                    }, 'image/png');
                });
            }).finally(function () {
                tombol.disabled = false;
            });
        });
    });
</script>
@endpush
@endsection

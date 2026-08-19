@extends('layout.v_layout')
@section('title', 'Dashboard')
@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0">Dashboard Anggaran</h3>
                <small class="text-secondary">Rekapitulasi per Satker &mdash; BWS Sulawesi IV</small>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                @if($lastSync)
                    <span class="text-secondary me-2">
                        <i class="bi bi-clock-history"></i>
                        Sinkron terakhir:
                        {{ \Illuminate\Support\Carbon::parse($lastSync)->timezone('Asia/Makassar')->translatedFormat('d M Y H:i') }}
                        WITA
                    </span>
                @endif
                <form action="{{ route('packets.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat"></i> Sinkron Data API
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        {{-- Kartu ringkasan total --}}
        <div class="row g-3 mb-2">
            <div class="col-12 col-md-6 col-xl">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3 style="font-size:1.5rem">{{ number_format($totals['jml_paket'], 0, ',', '.') }}
                        </h3>
                        <p>Total Paket Kegiatan</p>
                    </div>
                    <i class="small-box-icon bi bi-list-check"></i>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3 style="font-size:1.5rem">Rp
                            {{ number_format($totals['total_pagu'], 0, ',', '.') }}
                        </h3>
                        <p>Total Pagu</p>
                    </div>
                    <i class="small-box-icon bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3 style="font-size:1.5rem">Rp
                            {{ number_format($totals['total_realisasi'], 0, ',', '.') }}
                        </h3>
                        <p>Total Realisasi Keuangan</p>
                    </div>
                    <i class="small-box-icon bi bi-wallet2"></i>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3 style="font-size:1.5rem">{{ $totals['avg_progres_keuangan'] !== null ? number_format($totals['avg_progres_keuangan'], 2, ',', '.') . '%' : '–' }}
                        </h3>
                        <p>Rata-rata Progres Keuangan</p>
                    </div>
                    <i class="small-box-icon bi bi-graph-up"></i>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="small-box text-bg-secondary">
                    <div class="inner">
                        <h3 style="font-size:1.5rem">{{ $totals['avg_real_fisik'] !== null ? number_format($totals['avg_real_fisik'], 2, ',', '.') . '%' : '–' }}
                        </h3>
                        <p>Rata-rata Realisasi Fisik</p>
                    </div>
                    <i class="small-box-icon bi bi-bar-chart"></i>
                </div>
            </div>
        </div>

        {{-- Tabel rekap per satker --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-table me-1"></i> Rekapitulasi per Satker</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Nama Satker</th>
                                <th class="text-center">Jml Paket</th>
                                <th class="text-end">Nilai Pagu (Rp)</th>
                                <th class="text-end">Nilai Realisasi (Rp)</th>
                                <th class="text-end">Progres Keuangan</th>
                                <th class="text-end">Realisasi Fisik</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <span
                                            class="badge text-bg-secondary me-1">{{ $row['singkatan'] }}</span>
                                        {{ $row['nama'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($row['jml_paket'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($row['total_pagu'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($row['total_realisasi'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        @if ($row['avg_progres_keuangan'] !== null)
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height:6px; max-width:90px;"
                                                    role="progressbar">
                                                    <div class="progress-bar bg-info"
                                                        style="width: {{ min(100, $row['avg_progres_keuangan']) }}%">
                                                    </div>
                                                </div>
                                                <span class="fw-semibold"
                                                    style="min-width:52px">{{ number_format($row['avg_progres_keuangan'], 2, ',', '.') }}%</span>
                                            </div>
                                        @else
                                            <span class="text-secondary">&ndash;</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($row['avg_real_fisik'] !== null)
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height:6px; max-width:90px;"
                                                    role="progressbar">
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ min(100, $row['avg_real_fisik']) }}%">
                                                    </div>
                                                </div>
                                                <span class="fw-semibold"
                                                    style="min-width:52px">{{ number_format($row['avg_real_fisik'], 2, ',', '.') }}%</span>
                                            </div>
                                        @else
                                            <span class="text-secondary">&ndash;</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('satker.show', $row['slug']) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat Paket
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-secondary">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada data. Klik <strong>Sinkron Data API</strong> untuk menarik data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($rows))
                            <tfoot>
                                <tr class="fw-bold border-top">
                                    <td colspan="2" class="text-end">TOTAL / RATA-RATA</td>
                                    <td class="text-center">
                                        {{ number_format($totals['jml_paket'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($totals['total_pagu'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($totals['total_realisasi'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ $totals['avg_progres_keuangan'] !== null ? number_format($totals['avg_progres_keuangan'], 2, ',', '.') . '%' : '–' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $totals['avg_real_fisik'] !== null ? number_format($totals['avg_real_fisik'], 2, ',', '.') . '%' : '–' }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Progres per PPK & Peringkat --}}
        @php
            $rankBadge = function ($r) {
                if ($r === null) return '<span class="text-secondary">–</span>';
                $cls = $r == 1 ? 'text-bg-success' : ($r == 2 ? 'text-bg-primary' : ($r == 3 ? 'text-bg-info' : 'text-bg-light border'));
                return '<span class="badge ' . $cls . '">' . $r . '</span>';
            };
            $pct = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') . '%' : '–';
            $rb = fn ($v) => number_format($v / 1000, 0, ',', '.'); // ke ribuan (Rp.000)
            // Cell diwarnai merah jika nilainya di bawah acuan Ditjen SDA (input manual).
            $cellCls = fn ($v, $acuan) => ($v !== null && $acuan !== null && $v < $acuan) ? 'table-danger' : '';
        @endphp
        <div class="card mt-3">
            <div class="card-header position-relative">
                <button type="button" id="btnUnduhPpkTable" class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3">
                    <i class="bi bi-camera"></i> Unduh Gambar
                </button>
                <h3 class="card-title mb-0"><i class="bi bi-trophy me-1"></i> Progres per PPK &amp; Peringkat</h3>
            </div>
            <div class="card-body p-0">
                <style>
                    #ppkProgresTable { font-size: 13pt; }
                    #ppkProgresTable th,
                    #ppkProgresTable td { vertical-align: middle !important; }
                    /* Hilangkan garis tengah yang menembus sel rowspan (No., Satker/PPK, Pagu, Realisasi, Rank) di header. */
                    #ppkProgresTable thead tr:first-child th[rowspan] { border-bottom: 0; }
                </style>
                <div class="table-responsive" id="ppkProgresCapture">
                    <table id="ppkProgresTable" class="table table-bordered mb-0 text-nowrap" style="min-width:820px">
                        <thead class="table-light text-center">
                            <tr>
                                <th rowspan="2" style="width:44px">No.</th>
                                <th rowspan="2">Satker / PPK</th>
                                <th rowspan="2">Pagu<br><small>(Rp.000)</small></th>
                                <th rowspan="2">Realisasi<br><small>(Rp.000)</small></th>
                                <th colspan="2">Progres</th>
                                <th rowspan="2" style="width:70px">Rank</th>
                            </tr>
                            <tr>
                                <th style="width:90px">Keu (%)</th>
                                <th style="width:90px">Fis (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris acuan: Ditjen SDA (input manual) --}}
                            <tr class="table-primary fw-bold">
                                <td>–</td>
                                <td>Ditjen SDA</td>
                                <td>–</td>
                                <td>–</td>
                                <td>
                                    {{ $pct($ditjen['keu']) }}
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1 align-baseline"
                                            data-bs-toggle="modal" data-bs-target="#modalDitjen" title="Ubah nilai Ditjen SDA">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                                <td>{{ $pct($ditjen['fis']) }}</td>
                                <td>–</td>
                            </tr>
                            {{-- Baris acuan: total keseluruhan BWS Sulawesi IV Kendari --}}
                            <tr class="table-secondary fw-bold">
                                <td>–</td>
                                <td>BWS Sulawesi IV Kendari</td>
                                <td>{{ $rb($ppkProgres['total']['pagu']) }}</td>
                                <td>{{ $rb($ppkProgres['total']['realisasi']) }}</td>
                                <td class="{{ $cellCls($ppkProgres['total']['keu'], $ditjen['keu']) }}">{{ $pct($ppkProgres['total']['keu']) }}</td>
                                <td class="{{ $cellCls($ppkProgres['total']['fis'], $ditjen['fis']) }}">{{ $pct($ppkProgres['total']['fis']) }}</td>
                                <td>–</td>
                            </tr>
                            @forelse ($ppkProgres['satkers'] as $si => $s)
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
                                    <td colspan="7" class="py-4 text-secondary">Belum ada data PPK.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-center fw-bold border-top py-2 bg-white">
                        Status : {{ $statusJam['tanggal'] }} ; {{ $statusJam['jam'] }} WIB
                    </div>
                </div>
            </div>
            <div class="card-footer text-secondary small">
                Peringkat (Rank) dihitung berdasarkan <strong>Progres Keuangan</strong> tertinggi &mdash; baris satker diperingkat antar 5 satker, baris PPK diperingkat antar seluruh PPK. Cell berwarna merah menandakan progres di bawah acuan Ditjen SDA.
            </div>
        </div>

        {{-- Modal Ubah Progres Ditjen SDA --}}
        <div class="modal fade" id="modalDitjen" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('ditjen.update') }}" class="modal-content">
                    @csrf
                    <div class="modal-header text-bg-primary">
                        <h5 class="modal-title">Ubah Progres Ditjen SDA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Progres Keuangan (Keu %)</label>
                            <input type="number" step="0.01" min="0" max="100" name="keu" class="form-control"
                                   value="{{ old('keu', $ditjen['keu']) }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Realisasi Fisik (Fis %)</label>
                            <input type="number" step="0.01" min="0" max="100" name="fis" class="form-control"
                                   value="{{ old('fis', $ditjen['fis']) }}">
                        </div>
                        <div class="form-text">Nilai ini diinput manual sebagai acuan pembanding nasional Ditjen SDA. Kosongkan untuk menghapus nilai.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Deteksi paket yang belum dipetakan ke PPK (per satker) --}}
        <div class="card mt-3">
            <div class="card-header position-relative">
                <span class="text-secondary small position-absolute top-50 end-0 translate-middle-y me-3">Deteksi otomatis paket tanpa PPK, dikelompokkan per satker.</span>
                <h3 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i> Paket Belum Dipetakan ke PPK
                    @if ($unmapped['total'] > 0)
                        <span class="badge text-bg-danger ms-1">{{ number_format($unmapped['total'], 0, ',', '.') }}</span>
                    @endif
                </h3>
            </div>
            <div class="card-body @if ($unmapped['total'] === 0) @else p-0 @endif">
                @if ($unmapped['total'] === 0)
                    <div class="alert alert-success mb-0 d-flex align-items-end gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>Semua paket sudah dipetakan ke PPK. Tidak ada paket yang perlu ditetapkan.</div>
                    </div>
                @else
                    <div class="alert alert-warning m-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Terdapat <strong>{{ number_format($unmapped['total'], 0, ',', '.') }}</strong> paket yang belum memiliki PPK.
                            Buka halaman satker terkait lalu gunakan tombol <em>Perbarui PPK</em> untuk menetapkannya.
                        </div>
                    </div>

                    @foreach ($unmapped['satkers'] as $s)
                        <div class="px-3 pt-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                <h6 class="mb-0">
                                    <span class="badge text-bg-secondary me-1">{{ $s['singkatan'] }}</span>
                                    {{ $s['nama'] }}
                                    <span class="badge text-bg-danger ms-1">{{ $s['jml'] }} paket</span>
                                </h6>
                                <a href="{{ route('satker.show', $s['slug']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Tetapkan PPK
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-2">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:44px" class="text-center">#</th>
                                            <th style="min-width:230px">Kode Paket</th>
                                            <th style="white-space:normal">Nama Paket</th>
                                            <th class="text-end" style="width:150px">Pagu (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($s['packets'] as $k => $p)
                                            <tr>
                                                <td class="text-center">{{ $k + 1 }}</td>
                                                <td><code>{{ $p->kdpaket }}</code></td>
                                                <td style="white-space:normal">{{ $p->nmpaket }}</td>
                                                <td class="text-end">{{ number_format((float) $p->pagu, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Grafik Revisi Pagu per Satker --}}
        <div class="card mt-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h3 class="card-title mb-0"><i class="bi bi-bar-chart-steps me-1"></i> Riwayat Revisi Pagu per Satker</h3>
                <a href="{{ route('pagu.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Kelola Data
                </a>
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-2">Nilai dalam ribu rupiah (Rp).</p>
                @if ($paguChart['empty'])
                    <div class="text-center py-5 text-secondary">
                        <i class="bi bi-bar-chart fs-2 d-block mb-2"></i>
                        Belum ada data revisi pagu. Klik <strong>Kelola Data</strong> untuk menambahkan.
                    </div>
                @else
                    <div id="paguRevisiChart" style="min-height:1200px"></div>
                @endif
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->

@push('scripts')
@unless ($paguChart['empty'])
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const series     = @json($paguChart['series']);
        const categories = @json($paguChart['categories']);
        const colors     = @json($paguChart['colors']);
        const totals     = @json($paguChart['totals']);

        const fmt = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n));

        // Bungkus keterangan panjang menjadi beberapa baris agar terbaca horizontal
        // tanpa menabrak kolom sebelahnya.
        const wrap = (s, max) => {
            const words = String(s).split(/\s+/);
            const lines = [];
            let line = '';
            for (const w of words) {
                if (line && (line + ' ' + w).length > max) { lines.push(line); line = w; }
                else { line = line ? line + ' ' + w : w; }
            }
            if (line) lines.push(line);
            return lines;
        };
        const cats = categories.map((c) =>
            Array.isArray(c) ? [c[0], c[1], ...wrap(c[2], 22)] : c
        );

        const options = {
            chart: {
                type: 'bar',
                height: 1200,
                stacked: true,
                fontFamily: 'inherit',
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
            },
            series: series,
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 2,
                    dataLabels: {
                        // tampilkan label tiap segmen walau kecil, agar semua warna terbaca
                        hideOverflowingLabels: false,
                        total: {
                            enabled: true,
                            style: { fontSize: '12px', fontWeight: 700 },
                            formatter: (val) => fmt(val),
                        },
                    },
                },
            },
            dataLabels: {
                enabled: true,
                offsetY: 18, // geser label ke atas agar tidak menabrak batas segmen di bawahnya
                style: { colors: ['#fff'], fontSize: '10px', fontWeight: 400 },
                background: {
                    enabled: true,
                    foreColor: '#000',
                    opacity: 0.28,
                    padding: 3,
                    borderRadius: 3,
                    borderWidth: 0,
                },
                dropShadow: { enabled: false },
                formatter: function (val, opts) {
                    const total = totals[opts.dataPointIndex] || 0;
                    const pct = total ? (val / total * 100) : 0;
                    const name = opts.w.globals.seriesNames[opts.seriesIndex];
                    // label multi-baris: persen, nama satker, nilai
                    return [pct.toFixed(2).replace('.', ',') + '%', name, fmt(val)];
                },
            },
            stroke: { width: 1, colors: ['#fff'] },
            legend: { position: 'top', horizontalAlign: 'center', fontSize: '13px', markers: { width: 14, height: 14 } },
            grid: { padding: { bottom: 8 } },
            xaxis: {
                categories: cats,
                labels: {
                    rotate: 0,
                    rotateAlways: false,
                    trim: false,
                    hideOverflowingLabels: false,
                    style: { fontSize: '11px' },
                },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: { formatter: (v) => fmt(v) },
            },
            tooltip: {
                y: { formatter: (v) => 'Rp ' + fmt(v) + ' ribu' },
            },
        };

        const chart = new ApexCharts(document.querySelector('#paguRevisiChart'), options);
        chart.render();
    });
</script>
@endunless

<script>
    document.getElementById('btnUnduhPpkTable').addEventListener('click', function (ev) {
        const btn   = ev.currentTarget;
        const table = document.getElementById('ppkProgresCapture');

        btn.disabled = true;
        html2canvas(table, {
            scale: 2,
            backgroundColor: '#ffffff',
            // html2canvas tidak menangani sel <th rowspan> dengan benar (tetap
            // menggambar garis batas antar baris header menembus sel yang
            // menyatu). Ratakan header jadi satu baris khusus untuk hasil
            // capture agar garis itu tidak muncul; tampilan asli di halaman
            // tidak berubah karena ini hanya mengubah dokumen kloningan.
            onclone: function (clonedDoc) {
                const thead = clonedDoc.querySelector('#ppkProgresTable thead');
                if (thead) {
                    thead.innerHTML =
                        '<tr>' +
                        '<th style="width:44px">No.</th>' +
                        '<th>Satker / PPK</th>' +
                        '<th>Pagu<br><small>(Rp.000)</small></th>' +
                        '<th>Realisasi<br><small>(Rp.000)</small></th>' +
                        '<th style="width:90px">Keu (%)</th>' +
                        '<th style="width:90px">Fis (%)</th>' +
                        '<th style="width:70px">Rank</th>' +
                        '</tr>';
                }
            },
        }).then(function (canvas) {
            const link = document.createElement('a');
            link.download = 'progres-ppk-peringkat.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).finally(function () {
            btn.disabled = false;
        });
    });
</script>
@endpush

@endsection

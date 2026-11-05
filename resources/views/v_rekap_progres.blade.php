@extends('layout.v_layout')
@section('title', 'Rekap Progres')
@section('content')
@php
    $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v !== null ? number_format((float) $v, 2, ',', '.') . '%' : '–';
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">Rekap Progres</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 220px;">Jenis Paket</th>
                                    <th class="text-end">Jumlah Paket</th>
                                    <th class="text-end">Pagu (Rp)</th>
                                    <th class="text-end">Realisasi (Rp)</th>
                                    <th class="text-center">Progres Keu</th>
                                    <th class="text-center">Progres Fisik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jenis as $j)
                                    <tr>
                                        <td>{{ $j['judul'] }}</td>
                                        <td class="text-end">{{ $rp($j['total']['jml']) }}</td>
                                        <td class="text-end">{{ $rp($j['total']['pagu']) }}</td>
                                        <td class="text-end">{{ $rp($j['total']['realisasi']) }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $pct($j['total']['keu']) }}</span></td>
                                        <td class="text-center"><span class="badge bg-success">{{ $pct($j['total']['fis']) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-end">{{ $rp($total['jml']) }}</td>
                                    <td class="text-end">{{ $rp($total['pagu']) }}</td>
                                    <td class="text-end">{{ $rp($total['realisasi']) }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $pct($total['keu']) }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $pct($total['fis']) }}</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @foreach ($jenis as $j)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{ $j['judul'] }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 300px;">Satker / PPK</th>
                                        <th class="text-end">Jumlah Paket</th>
                                        <th class="text-end">Pagu (Rp)</th>
                                        <th class="text-end">Realisasi (Rp)</th>
                                        <th class="text-center">Progres Keu</th>
                                        <th class="text-center">Progres Fisik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($j['satker'] as $s)
                                        <tr class="fw-semibold">
                                            <td>{{ $s['nama'] }}</td>
                                            <td class="text-end">{{ $rp($s['total']['jml']) }}</td>
                                            <td class="text-end">{{ $rp($s['total']['pagu']) }}</td>
                                            <td class="text-end">{{ $rp($s['total']['realisasi']) }}</td>
                                            <td class="text-center"><span class="badge bg-primary">{{ $pct($s['total']['keu']) }}</span></td>
                                            <td class="text-center"><span class="badge bg-success">{{ $pct($s['total']['fis']) }}</span></td>
                                        </tr>
                                        @foreach ($s['ppk'] as $p)
                                            <tr>
                                                <td class="ps-5">{{ $p['nama'] }}</td>
                                                <td class="text-end">{{ $rp($p['jml']) }}</td>
                                                <td class="text-end">{{ $rp($p['pagu']) }}</td>
                                                <td class="text-end">{{ $rp($p['realisasi']) }}</td>
                                                <td class="text-center">{{ $pct($p['keu']) }}</td>
                                                <td class="text-center">{{ $pct($p['fis']) }}</td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">Tidak ada data paket</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($j['satker'] !== [])
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td>Total {{ $j['judul'] }}</td>
                                            <td class="text-end">{{ $rp($j['total']['jml']) }}</td>
                                            <td class="text-end">{{ $rp($j['total']['pagu']) }}</td>
                                            <td class="text-end">{{ $rp($j['total']['realisasi']) }}</td>
                                            <td class="text-center"><span class="badge bg-primary">{{ $pct($j['total']['keu']) }}</span></td>
                                            <td class="text-center"><span class="badge bg-success">{{ $pct($j['total']['fis']) }}</span></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>
@endsection

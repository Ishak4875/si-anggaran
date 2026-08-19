@extends('layout.v_layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Paket Reguler</h3>
                </div>
                <div class="card-body">
                    @foreach ($data as $satkerSlug => $satkerData)
                        <div class="mb-4">
                            <!-- Header Satker -->
                            <div class="bg-light border-bottom border-2 border-dark py-2 px-3 mb-0">
                                <h5 class="mb-0 fw-bold">{{ $satkerData['name'] }}</h5>
                            </div>

                            <!-- Tabel Paket -->
                            <table class="table table-sm table-hover mb-0 border">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 12%;">Kode Paket</th>
                                        <th style="width: 30%;">Nama Paket</th>
                                        <th style="width: 15%;">PPK</th>
                                        <th style="width: 12%;" class="text-end">Pagu (Rp)</th>
                                        <th style="width: 12%;" class="text-end">Realisasi (Rp)</th>
                                        <th style="width: 8%;" class="text-center">Progres Keu</th>
                                        <th style="width: 8%;" class="text-center">Progres Fisik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($satkerData['packets'] as $index => $packet)
                                        @php
                                            $relatedPackets = $packet->getRelatedPackets();
                                            $ppkNames = $relatedPackets
                                                ->pluck('ppk.nama')
                                                ->unique()
                                                ->implode(', ');
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $packet->kode_paket }}</td>
                                            <td>{{ $packet->nama_paket }}</td>
                                            <td>{{ $ppkNames ?: '-' }}</td>
                                            <td class="text-end">{{ number_format($packet->getPagu(), 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($packet->getRealisasi(), 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $packet->getProgresKeu() }}%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $packet->getProgresFisik() }}%</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-3">
                                                Tidak ada data paket untuk satker ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    @if (count($data) === 0)
                        <div class="alert alert-info">
                            Belum ada data paket reguler. Silakan lakukan seeding data terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

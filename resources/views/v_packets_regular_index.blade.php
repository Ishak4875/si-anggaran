@extends('layout.v_layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <h3 class="card-title mb-0">Daftar Paket Reguler</h3>
                    <div class="input-group input-group-sm ms-auto" style="max-width:350px">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="cariPaket" class="form-control"
                               placeholder="Cari nama / kode paket..." autocomplete="off">
                        <button type="button" id="cariReset" class="btn btn-outline-secondary" title="Bersihkan">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    @foreach ($data as $satkerSlug => $satkerData)
                        <div class="mb-4 satker-section" data-satker="{{ $satkerSlug }}">
                            <!-- Header Satker -->
                            <div class="bg-light border-bottom border-2 border-dark py-2 px-3 mb-0 satker-header">
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
                                <tbody class="paket-body">
                                    @forelse ($satkerData['packets'] as $index => $packet)
                                        @php
                                            $relatedPackets = $packet->getRelatedPackets();
                                            $ppkNames = $relatedPackets
                                                ->pluck('ppk.nama')
                                                ->unique()
                                                ->implode(', ');
                                        @endphp
                                        <tr class="paket-row" data-cari="{{ Str::lower($packet->kode_paket . ' ' . $packet->nama_paket) }}">
                                            <td class="nomor">{{ $index + 1 }}</td>
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
                                        <tr class="text-center text-muted py-3">
                                            <td colspan="8">
                                                Tidak ada data paket untuk satker ini
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr id="barisKosong" class="d-none">
                                        <td colspan="8" class="text-center py-4 text-secondary">
                                            <i class="bi bi-search me-1"></i> Tidak ada paket yang cocok dengan pencarian.
                                        </td>
                                    </tr>
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

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('cariPaket');
        const reset = document.getElementById('cariReset');
        const satkerSections = Array.from(document.querySelectorAll('.satker-section'));
        const rows = Array.from(document.querySelectorAll('.paket-row'));
        const kosong = document.getElementById('barisKosong');

        function filter() {
            const q = input.value.trim().toLowerCase();
            let totalVisible = 0;

            satkerSections.forEach(section => {
                const sectionRows = Array.from(section.querySelectorAll('.paket-row'));
                let sectionVisible = 0;

                sectionRows.forEach((row, idx) => {
                    const cocok = q === '' || row.dataset.cari.includes(q);
                    row.classList.toggle('d-none', !cocok);
                    if (cocok) {
                        sectionVisible++;
                        totalVisible++;
                        row.querySelector('.nomor').textContent = sectionVisible;
                    }
                });

                const header = section.querySelector('.satker-header');
                header.classList.toggle('d-none', sectionVisible === 0);
                const table = section.querySelector('table');
                table.classList.toggle('d-none', sectionVisible === 0);
            });

            kosong.classList.toggle('d-none', totalVisible !== 0 || q === '');
        }

        input.addEventListener('input', filter);
        reset.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    })();
</script>
@endpush

@extends('layout.v_layout')
@section('title', 'Daftar Paket Inpres')
@section('content')
@php
    $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v !== null ? number_format((float) $v, 2, ',', '.') . '%' : '–';
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <h3 class="card-title mb-0">Daftar Paket Inpres</h3>
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

                    @foreach ($data as $slug => $satker)
                        <div class="mb-4 satker-section">
                            <div class="d-flex flex-wrap align-items-center gap-2 bg-light border-bottom border-2 border-dark py-2 px-3 satker-header">
                                <span class="badge text-bg-secondary">{{ $satker['singkatan'] }}</span>
                                <h5 class="mb-0 fw-bold me-auto">{{ $satker['name'] }}</h5>
                                <span class="badge text-bg-danger">{{ $satker['packets']->count() }} paket</span>
                                @if ($satker['tanpa_ppk'] > 0)
                                    <span class="badge text-bg-warning" title="Paket yang PPK-nya belum ditetapkan">
                                        {{ $satker['tanpa_ppk'] }} belum ada PPK
                                    </span>
                                @endif
                                <a href="{{ route('satker.show', $slug) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right"></i> Tetapkan PPK
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 border align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>Kode Paket</th>
                                            <th>Nama Paket</th>
                                            <th>PPK</th>
                                            <th class="text-end">Pagu (Rp)</th>
                                            <th class="text-end">Realisasi (Rp)</th>
                                            <th class="text-end">Keu (%)</th>
                                            <th class="text-end">Fis (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($satker['packets'] as $i => $p)
                                            <tr class="paket-row" data-cari="{{ Str::lower($p->kdpaket . ' ' . $p->nmpaket) }}">
                                                <td class="nomor">{{ $i + 1 }}</td>
                                                <td class="text-nowrap"><code>{{ $p->kdpaket }}</code></td>
                                                <td>{{ $p->nmpaket }}</td>
                                                <td>
                                                    @if ($p->ppk)
                                                        {{ $p->ppk->jabatan }}
                                                    @else
                                                        <span class="badge text-bg-warning">Belum ditetapkan</span>
                                                    @endif
                                                </td>
                                                <td class="text-end text-nowrap">{{ $rp($p->pagu) }}</td>
                                                <td class="text-end text-nowrap">{{ $rp($p->realisasi) }}</td>
                                                <td class="text-end">{{ $pct((float) $p->pagu > 0 ? $p->realisasi / $p->pagu * 100 : null) }}</td>
                                                <td class="text-end">{{ $pct($p->real_fisik) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-secondary py-3">
                                                    Belum ada paket Inpres untuk satker ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if ($satker['packets']->isNotEmpty())
                                        <tfoot class="table-light fw-semibold">
                                            <tr>
                                                <td colspan="4" class="text-end">Total {{ $satker['singkatan'] }}</td>
                                                <td class="text-end text-nowrap">{{ $rp($satker['pagu']) }}</td>
                                                <td class="text-end text-nowrap">{{ $rp($satker['realisasi']) }}</td>
                                                <td class="text-end">{{ $pct($satker['keu']) }}</td>
                                                <td class="text-end">{{ $pct($satker['fis']) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div id="barisKosong" class="d-none text-center py-4 text-secondary">
                        <i class="bi bi-search me-1"></i> Tidak ada paket yang cocok dengan pencarian.
                    </div>

                    <p class="small text-secondary mb-0">
                        Paket Inpres dikenali dari awalan kode paket (diatur di <code>config/inpres.php</code>).
                        Keu = Σ realisasi / Σ pagu; Fis = rata-rata realisasi fisik berbobot pagu.
                    </p>
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
        const sections = Array.from(document.querySelectorAll('.satker-section'));
        const kosong = document.getElementById('barisKosong');

        function filter() {
            const q = input.value.trim().toLowerCase();
            let totalVisible = 0;

            sections.forEach(section => {
                let visible = 0;
                section.querySelectorAll('.paket-row').forEach(row => {
                    const cocok = q === '' || row.dataset.cari.includes(q);
                    row.classList.toggle('d-none', !cocok);
                    if (cocok) row.querySelector('.nomor').textContent = ++visible;
                });
                totalVisible += visible;
                // Saat mencari, sembunyikan satker tanpa hasil (total satker tetap dihitung dari semua paket)
                section.classList.toggle('d-none', q !== '' && visible === 0);
            });

            kosong.classList.toggle('d-none', totalVisible !== 0 || q === '');
        }

        input.addEventListener('input', filter);
        reset.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    })();
</script>
@endpush

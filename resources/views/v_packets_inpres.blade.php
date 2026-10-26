@extends('layout.v_layout')
@section('title', 'Daftar Paket Inpres')
@section('content')
@php
    $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v !== null ? number_format((float) $v, 2, ',', '.') . '%' : '–';
    // Pilihan filter PPK: per satker, hanya PPK yang punya paket Inpres
    $ppkOptions = collect($data)->map(fn ($s) => $s['packets']->pluck('ppk.jabatan')->filter()->unique()->sort()->values());
    $adaTanpaPpk = collect($data)->sum('tanpa_ppk') > 0;
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <h3 class="card-title mb-0">Daftar Paket Inpres</h3>
                    <select id="filterPpk" class="form-select form-select-sm ms-auto" style="max-width:280px" aria-label="Filter PPK">
                        <option value="">Semua PPK</option>
                        @foreach ($ppkOptions as $slug => $jabatans)
                            @if ($jabatans->isNotEmpty())
                                <optgroup label="{{ $data[$slug]['singkatan'] }}">
                                    @foreach ($jabatans as $jabatan)
                                        <option value="{{ $jabatan }}">{{ $jabatan }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                        @if ($adaTanpaPpk)
                            <option value="-">Belum ditetapkan</option>
                        @endif
                    </select>
                    <div class="input-group input-group-sm" style="max-width:350px">
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
                        <div class="mb-4 satker-section" data-satker="{{ $slug }}">
                            <!-- Header Satker (format sama dengan Daftar Paket Reguler) -->
                            <div class="d-flex flex-wrap align-items-center gap-2 bg-light border-bottom border-2 border-dark py-2 px-3 mb-0 satker-header">
                                <h5 class="mb-0 fw-bold me-auto">{{ $satker['name'] }}</h5>
                                @if ($satker['tanpa_ppk'] > 0)
                                    <span class="badge text-bg-warning" title="Paket yang PPK-nya belum ditetapkan">
                                        {{ $satker['tanpa_ppk'] }} belum ada PPK
                                    </span>
                                @endif
                                <a href="{{ route('satker.show', $slug) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right"></i> Tetapkan PPK
                                </a>
                            </div>

                            <!-- Tabel Paket -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 border">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;">No</th>
                                            <th style="width: 12%;">Kode Paket</th>
                                            <th style="width: 30%; min-width: 260px;">Nama Paket</th>
                                            <th style="width: 15%;">PPK</th>
                                            <th style="width: 12%;" class="text-end">Pagu (Rp)</th>
                                            <th style="width: 12%;" class="text-end">Realisasi (Rp)</th>
                                            <th style="width: 8%;" class="text-center">Progres Keu</th>
                                            <th style="width: 8%;" class="text-center">Progres Fisik</th>
                                        </tr>
                                    </thead>
                                    <tbody class="paket-body">
                                        @forelse ($satker['packets'] as $i => $p)
                                            <tr class="paket-row" data-cari="{{ Str::lower($p->kdpaket . ' ' . $p->nmpaket) }}"
                                                data-ppk="{{ $p->ppk->jabatan ?? '-' }}"
                                                data-pagu="{{ (float) $p->pagu }}" data-real="{{ (float) $p->realisasi }}"
                                                data-fis="{{ (float) $p->real_fisik }}">
                                                <td class="nomor">{{ $i + 1 }}</td>
                                                <td>{{ $p->kdpaket }}</td>
                                                <td>{{ $p->nmpaket }}</td>
                                                <td>{{ $p->ppk->jabatan ?? '-' }}</td>
                                                <td class="text-end">{{ $rp($p->pagu) }}</td>
                                                <td class="text-end">{{ $rp($p->realisasi) }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ $pct((float) $p->pagu > 0 ? $p->realisasi / $p->pagu * 100 : null) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">{{ $pct($p->real_fisik) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center text-muted py-3">
                                                <td colspan="8">
                                                    Tidak ada data paket untuk satker ini
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if ($satker['packets']->isNotEmpty())
                                        <tfoot class="table-light fw-semibold">
                                            <tr>
                                                <td colspan="4">Total {{ $satker['singkatan'] }}<span class="total-ket fw-normal"></span></td>
                                                <td class="text-end total-pagu">{{ $rp($satker['pagu']) }}</td>
                                                <td class="text-end total-real">{{ $rp($satker['realisasi']) }}</td>
                                                <td class="text-center"><span class="badge bg-primary total-keu">{{ $pct($satker['keu']) }}</span></td>
                                                <td class="text-center"><span class="badge bg-success total-fis">{{ $pct($satker['fis']) }}</span></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div id="barisKosong" class="d-none text-center py-4 text-secondary">
                        <i class="bi bi-search me-1"></i> Tidak ada paket yang cocok dengan pencarian / filter PPK.
                    </div>
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
        const pilihPpk = document.getElementById('filterPpk');
        const sections = Array.from(document.querySelectorAll('.satker-section'));
        const kosong = document.getElementById('barisKosong');

        const rp = (v) => Math.round(v).toLocaleString('id-ID');
        const pct = (v) => v === null ? '–' : v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';

        function filter() {
            const q = input.value.trim().toLowerCase();
            const ppk = pilihPpk.value;
            const aktif = q !== '' || ppk !== '';
            let totalVisible = 0;

            sections.forEach(section => {
                let visible = 0, pagu = 0, real = 0, fisBobot = 0;
                section.querySelectorAll('.paket-row').forEach(row => {
                    const cocok = (q === '' || row.dataset.cari.includes(q))
                        && (ppk === '' || row.dataset.ppk === ppk);
                    row.classList.toggle('d-none', !cocok);
                    if (!cocok) return;
                    row.querySelector('.nomor').textContent = ++visible;
                    const p = +row.dataset.pagu;
                    pagu += p;
                    real += +row.dataset.real;
                    fisBobot += +row.dataset.fis * p;
                });
                totalVisible += visible;
                section.classList.toggle('d-none', aktif && visible === 0);

                // Total mengikuti paket yang tampil (konvensi Keu/Fis sama dengan server)
                const foot = section.querySelector('tfoot');
                if (foot) {
                    foot.querySelector('.total-ket').textContent = aktif ? ' (' + visible + ' paket tersaring)' : '';
                    foot.querySelector('.total-pagu').textContent = rp(pagu);
                    foot.querySelector('.total-real').textContent = rp(real);
                    foot.querySelector('.total-keu').textContent = pct(pagu > 0 ? real / pagu * 100 : null);
                    foot.querySelector('.total-fis').textContent = pct(pagu > 0 ? fisBobot / pagu : null);
                }
            });

            kosong.classList.toggle('d-none', totalVisible !== 0 || !aktif);
        }

        input.addEventListener('input', filter);
        pilihPpk.addEventListener('change', filter);
        reset.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    })();
</script>
@endpush

@extends('layout.v_layout')
@section('title', $group['singkatan'] . ' - Paket Kegiatan')
@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">
                    <span class="badge text-bg-primary me-1">{{ $group['singkatan'] }}</span>
                    {{ $group['nama'] }}
                </h3>
                <small class="text-secondary">
                    Kode satker: {{ implode(', ', $group['kdsatker']) }}
                </small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        {{-- Ringkasan satker --}}
        <div class="row g-3 mb-2">
            <div class="col-6 col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-primary"><i class="bi bi-list-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Jumlah Paket</span>
                        <span class="info-box-number">{{ number_format($ringkas['jml_paket'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-success"><i class="bi bi-cash-stack"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Pagu</span>
                        <span class="info-box-number">Rp {{ number_format($ringkas['total_pagu'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-info"><i class="bi bi-graph-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rata-rata Progres Keuangan</span>
                        <span class="info-box-number">{{ $ringkas['avg_keu'] !== null ? number_format($ringkas['avg_keu'], 2, ',', '.') . '%' : '–' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-warning"><i class="bi bi-bar-chart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rata-rata Realisasi Fisik</span>
                        <span class="info-box-number">{{ $ringkas['avg_fisik'] !== null ? number_format($ringkas['avg_fisik'], 2, ',', '.') . '%' : '–' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel seluruh paket kegiatan --}}
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h3 class="card-title mb-0"><i class="bi bi-table me-1"></i> Daftar Paket Kegiatan</h3>
                <div class="input-group input-group-sm ms-auto" style="max-width:300px">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="cariPaket" class="form-control"
                           placeholder="Cari nama / kode paket..." autocomplete="off">
                    <button type="button" id="cariReset" class="btn btn-outline-secondary" title="Bersihkan">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelPaket" class="table table-striped table-hover text-nowrap mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Kode Paket</th>
                                <th style="white-space:normal; min-width:260px">Nama Paket</th>
                                <th style="min-width:200px">PPK</th>
                                <th class="text-end">Pagu (Rp)</th>
                                <th class="text-end">Realisasi (Rp)</th>
                                <th class="text-end">Progres Keu.</th>
                                <th class="text-end">Real. Fisik</th>
                                <th class="text-center" style="width:140px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="paketBody">
                            @forelse ($packets as $i => $p)
                                <tr class="paket-row" data-cari="{{ Str::lower($p->kdpaket . ' ' . $p->nmpaket) }}">
                                    <td class="nomor">{{ $i + 1 }}</td>
                                    <td><code>{{ $p->kdpaket }}</code></td>
                                    <td style="white-space:normal">{{ $p->nmpaket }}</td>
                                    <td>
                                        @if ($p->ppk)
                                            {{ $p->ppk->jabatan }}
                                        @else
                                            <span class="badge text-bg-light border text-secondary">Belum ditetapkan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format((float) $p->pagu, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $p->realisasi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        {{ $p->progres_keuangan !== null ? number_format($p->progres_keuangan, 2, ',', '.') . '%' : '–' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $p->real_fisik !== null ? number_format($p->real_fisik, 2, ',', '.') . '%' : '–' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-warning text-white btn-perbarui-ppk"
                                                data-bs-toggle="modal" data-bs-target="#modalPpk"
                                                data-id="{{ $p->id }}"
                                                data-kode="{{ $p->kdpaket }}"
                                                data-nama="{{ $p->nmpaket }}"
                                                data-ppk="{{ $p->ppk_id }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui PPK
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-secondary">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada paket untuk satker ini. Lakukan sinkronisasi dari Dashboard.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="barisKosong" class="d-none">
                                <td colspan="9" class="text-center py-4 text-secondary">
                                    <i class="bi bi-search me-1"></i> Tidak ada paket yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-secondary small">
                Menampilkan <span id="jmlTampil">{{ $packets->count() }}</span> dari {{ $packets->count() }} paket.
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->

{{-- ============ Modal Perbarui PPK ============ --}}
<div class="modal fade" id="modalPpk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('satker.assignPpk', $slug) }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-warning">
                <h5 class="modal-title">Perbarui PPK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <div class="text-secondary small">Kode Paket</div>
                    <div><code id="mppkKode"></code></div>
                </div>
                <div class="mb-3">
                    <div class="text-secondary small">Nama Paket</div>
                    <div id="mppkNama" style="white-space:normal"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label">PPK</label>
                    <select id="mppkSelect" class="form-select">
                        <option value="">— Belum ditetapkan —</option>
                        @foreach ($ppkOptions as $opt)
                            <option value="{{ $opt->id }}">{{ $opt->jabatan }}@if ($opt->nama) — {{ $opt->nama }}@endif</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pilihan hanya PPK pada satker {{ $group['singkatan'] }}.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-warning text-white"><i class="bi bi-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const input   = document.getElementById('cariPaket');
        const reset   = document.getElementById('cariReset');
        const rows    = Array.from(document.querySelectorAll('#paketBody .paket-row'));
        const kosong  = document.getElementById('barisKosong');
        const jmlSpan = document.getElementById('jmlTampil');

        function filter() {
            const q = input.value.trim().toLowerCase();
            let tampil = 0;

            rows.forEach((row) => {
                const cocok = q === '' || row.dataset.cari.includes(q);
                row.classList.toggle('d-none', !cocok);
                if (cocok) {
                    tampil++;
                    // perbarui nomor urut mengikuti hasil filter
                    row.querySelector('.nomor').textContent = tampil;
                }
            });

            kosong.classList.toggle('d-none', tampil !== 0);
            if (jmlSpan) jmlSpan.textContent = tampil;
        }

        input.addEventListener('input', filter);
        reset.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    })();

    // Modal Perbarui PPK: isi data dari tombol yang diklik.
    (function () {
        const modal  = document.getElementById('modalPpk');
        const kode   = document.getElementById('mppkKode');
        const nama   = document.getElementById('mppkNama');
        const select = document.getElementById('mppkSelect');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget;
            if (!btn) return;
            const id      = btn.getAttribute('data-id');
            const current = btn.getAttribute('data-ppk') || '';

            kode.textContent = btn.getAttribute('data-kode') || '';
            nama.textContent = btn.getAttribute('data-nama') || '';

            // arahkan select ke paket ini + set nilai PPK saat ini
            select.name  = 'ppk[' + id + ']';
            select.value = current;
        });
    })();
</script>
@endpush

@endsection

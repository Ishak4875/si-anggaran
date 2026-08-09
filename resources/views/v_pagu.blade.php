@extends('layout.v_layout')
@section('title', 'Kelola Revisi Pagu')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Kelola Data Revisi Pagu</h3>
                <small class="text-secondary">Data ini menjadi sumber grafik "Riwayat Revisi Pagu" pada dashboard. Nilai dalam ribu rupiah.</small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pagu.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title mb-0"><i class="bi bi-table me-1"></i> Tabel Revisi Pagu</h3>
                    <div class="d-flex gap-2">
                        <button type="button" id="tambahBaris" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg"></i> Tambah Revisi
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle" style="min-width:900px">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px" class="text-center">#</th>
                                    <th style="width:150px">Tanggal</th>
                                    <th style="min-width:240px">Keterangan</th>
                                    @foreach ($groups as $slug => $g)
                                        <th class="text-end" style="min-width:130px">
                                            <span class="badge" style="background-color: {{ $g['warna'] }}">{{ $g['singkatan'] }}</span>
                                        </th>
                                    @endforeach
                                    <th class="text-end" style="min-width:140px">Total</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="barisPagu">
                                @forelse ($revisions as $i => $rev)
                                    <tr class="baris-revisi">
                                        <td class="text-center nomor">{{ $i + 1 }}</td>
                                        <td>
                                            <input type="date" name="revisions[{{ $i }}][tanggal]"
                                                   value="{{ optional($rev->tanggal)->format('Y-m-d') }}"
                                                   class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="text" name="revisions[{{ $i }}][keterangan]"
                                                   value="{{ $rev->keterangan }}"
                                                   class="form-control form-control-sm" placeholder="mis. Pagu Awal">
                                        </td>
                                        @foreach ($groups as $slug => $g)
                                            <td>
                                                <input type="number" step="any" min="0"
                                                       name="revisions[{{ $i }}][nilai][{{ $slug }}]"
                                                       value="{{ $rev->nilai[$slug] ?? 0 }}"
                                                       class="form-control form-control-sm text-end nilai-input">
                                            </td>
                                        @endforeach
                                        <td class="text-end fw-semibold total-baris">0</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger hapus-baris" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-secondary small">
                    Tips: kosongkan kolom <em>Keterangan</em> untuk mengabaikan baris. Klik <strong>Simpan</strong> untuk menyimpan seluruh perubahan.
                </div>
            </div>
        </form>

    </div>
</div>

{{-- Template baris kosong untuk tombol Tambah --}}
<template id="templateBaris">
    <tr class="baris-revisi">
        <td class="text-center nomor"></td>
        <td>
            <input type="date" name="revisions[__IDX__][tanggal]" class="form-control form-control-sm">
        </td>
        <td>
            <input type="text" name="revisions[__IDX__][keterangan]" class="form-control form-control-sm" placeholder="mis. Revisi ...">
        </td>
        @foreach ($groups as $slug => $g)
            <td>
                <input type="number" step="any" min="0" name="revisions[__IDX__][nilai][{{ $slug }}]" value="0" class="form-control form-control-sm text-end nilai-input">
            </td>
        @endforeach
        <td class="text-end fw-semibold total-baris">0</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger hapus-baris" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    (function () {
        const tbody    = document.getElementById('barisPagu');
        const template = document.getElementById('templateBaris');
        const btnAdd   = document.getElementById('tambahBaris');
        const fmt      = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
        let nextIdx    = {{ $revisions->count() }};

        function hitungTotalBaris(tr) {
            let total = 0;
            tr.querySelectorAll('.nilai-input').forEach((inp) => {
                total += parseFloat(inp.value) || 0;
            });
            tr.querySelector('.total-baris').textContent = fmt(total);
        }

        function perbaruiNomor() {
            tbody.querySelectorAll('.baris-revisi').forEach((tr, i) => {
                tr.querySelector('.nomor').textContent = i + 1;
            });
        }

        function pasangEvent(tr) {
            tr.querySelectorAll('.nilai-input').forEach((inp) => {
                inp.addEventListener('input', () => hitungTotalBaris(tr));
            });
            tr.querySelector('.hapus-baris').addEventListener('click', () => {
                tr.remove();
                perbaruiNomor();
            });
            hitungTotalBaris(tr);
        }

        // pasang event pada baris awal
        tbody.querySelectorAll('.baris-revisi').forEach(pasangEvent);

        btnAdd.addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__IDX__', nextIdx++);
            const tmp  = document.createElement('tbody');
            tmp.innerHTML = html.trim();
            const tr = tmp.firstElementChild;
            tbody.appendChild(tr);
            pasangEvent(tr);
            perbaruiNomor();
        });
    })();
</script>
@endpush

@endsection

@extends('layout.v_layout')
@section('title', 'Monitoring Pelaporan Kekeringan')
@section('content')
@php
    $teksStatus = $info->tanggal
        ? $info->tanggal->translatedFormat('d F') . ($info->jam ? ' Pukul ' . substr($info->jam, 0, 5) : '') . ' WITA'
        : '-';
@endphp

<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Monitoring Pelaporan Kekeringan</h3>
        <small class="text-secondary">Klik ikon untuk mengubah status / jenis pelaporan, lalu tekan Simpan.</small>
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

        <form method="POST" action="{{ route('pelaporan-kekeringan.update') }}" id="formPelaporan">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label class="form-label small mb-1" for="inputTanggal">Tanggal status</label>
                        <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-sm"
                               value="{{ $info->tanggal?->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="form-label small mb-1" for="inputJam">Jam (WITA)</label>
                        <input type="time" name="jam" id="inputJam" class="form-control form-control-sm"
                               value="{{ $info->jam ? substr($info->jam, 0, 5) : '' }}">
                    </div>
                    <button type="button" id="btnSekarang" class="btn btn-sm btn-outline-secondary" title="Isi dengan waktu sekarang (WITA)">
                        <i class="bi bi-clock"></i> Sekarang
                    </button>

                    <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                        <span id="belumDisimpan" class="badge text-bg-warning d-none">Belum disimpan</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalReset">
                            <i class="bi bi-arrow-counterclockwise"></i> Refresh
                        </button>
                        <button type="button" id="btnUnduh" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera"></i> Unduh Gambar
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Area yang ikut di "Unduh Gambar" --}}
                        <div id="areaLaporan" class="bg-white p-2" style="min-width: 900px;">
                            <div class="text-center mb-2">
                                <div class="fw-bold fs-5">List Pelaporan</div>
                                <div>Status : <span id="teksStatus">{{ $teksStatus }}</span></div>
                            </div>

                            <table class="table table-bordered table-sm align-middle mb-2 tabel-lapor">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center" style="width: 60px;">No.</th>
                                        <th rowspan="2" class="text-center">Nama</th>
                                        <th rowspan="2" class="text-center">Jabatan</th>
                                        <th rowspan="2" class="text-center" style="width: 80px;">Status</th>
                                        <th colspan="2" class="text-center">Jenis Pelaporan</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="width: 130px;">Kejadian Baru</th>
                                        <th class="text-center" style="width: 130px;">Penanganan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($grup as $g)
                                        <tr class="baris-grup">
                                            <td class="fw-bold">{{ $g['kode'] }}</td>
                                            <td colspan="2" class="fw-bold">{{ $g['nama'] }}</td>
                                            <td></td><td></td><td></td>
                                        </tr>
                                        @foreach ($g['anggota'] as $a)
                                            @php
                                                $s = $status[$a['kode']] ?? null;
                                                $id = str_replace('.', '_', $a['kode']);
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ \Illuminate\Support\Str::afterLast($a['kode'], '.') }}</td>
                                                <td>{{ $a['nama'] }}</td>
                                                <td class="text-center">{{ $a['jabatan'] }}</td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="btn-check cek-status" autocomplete="off"
                                                           name="lapor[{{ $a['kode'] }}][sudah_lapor]" value="1" id="st_{{ $id }}"
                                                           data-baris="{{ $id }}" @checked($s?->sudah_lapor)>
                                                    <label class="tombol-lapor" for="st_{{ $id }}" title="Klik untuk mengubah status">
                                                        <i class="bi bi-x-lg text-danger ikon-belum"></i>
                                                        <i class="bi bi-check-square text-success ikon-sudah"></i>
                                                    </label>
                                                </td>
                                                @foreach (['kejadian_baru' => 'Kejadian Baru', 'penanganan' => 'Penanganan'] as $field => $label)
                                                    <td class="text-center">
                                                        <input type="checkbox" class="btn-check cek-jenis" autocomplete="off"
                                                               name="lapor[{{ $a['kode'] }}][{{ $field }}]" value="1" id="{{ $field }}_{{ $id }}"
                                                               data-baris="{{ $id }}" @checked($s?->{$field})>
                                                        <label class="tombol-lapor" for="{{ $field }}_{{ $id }}" title="{{ $label }}">
                                                            <i class="bi bi-square ikon-kosong"></i>
                                                            <i class="bi bi-check-square text-success ikon-sudah"></i>
                                                        </label>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="small">
                                <div><i class="bi bi-x-lg text-danger me-2"></i>Belum Melaporkan</div>
                                <div><i class="bi bi-check-square text-success me-2"></i>Sudah Melaporkan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

{{-- ============ Modal Refresh ============ --}}
<div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('pelaporan-kekeringan.reset') }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-danger">
                <h5 class="modal-title">Refresh Status Pelaporan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Semua petugas akan dikembalikan ke <strong>Belum Melaporkan</strong> (<i class="bi bi-x-lg text-danger"></i>)
                dan centang jenis pelaporan dikosongkan. Lanjutkan?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="submit" class="btn btn-danger">Ya, Refresh</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<style>
    .tabel-lapor { color: #212529; }
    /* Garis hitam seperti lembar aslinya; warna garis tema (krem pucat) hampir tak terlihat di gambar unduhan */
    /* Garis hitam penuh di setiap sel (4 sisi). Di .table-bordered garis mendatar & bingkai luar
       milik baris/tabel, dan html2canvas tidak menggambar garis baris dengan benar. */
    .tabel-lapor { border: 0 !important; border-collapse: collapse; }
    .tabel-lapor > :not(caption) > * { border: 0 !important; }
    .tabel-lapor > :not(caption) > * > * { border: 1px solid #000 !important; }
    .tabel-lapor th { background: #fff; }
    .tabel-lapor .baris-grup td { background: #d9e1f2; }
    .tombol-lapor { cursor: pointer; font-size: 1.15rem; line-height: 1; padding: .1rem .35rem; margin: 0; user-select: none; }
    .tombol-lapor .ikon-kosong { color: #ced4da; }
    /* Ikon tegas seperti laporan aslinya (palet tema sengaja redup, di sini tidak dipakai) */
    #areaLaporan .bi-x-lg { color: #e53935 !important; -webkit-text-stroke: 1.5px #e53935; }
    #areaLaporan .bi-check-square { color: #2e7d32 !important; -webkit-text-stroke: .6px #2e7d32; }
    /* Status: ✗ saat belum, ☑ saat sudah. Jenis: kotak samar saat belum, ☑ saat sudah. */
    .btn-check + .tombol-lapor .ikon-sudah { display: none; }
    .btn-check:checked + .tombol-lapor .ikon-sudah { display: inline; }
    .btn-check:checked + .tombol-lapor .ikon-belum,
    .btn-check:checked + .tombol-lapor .ikon-kosong { display: none; }
    .btn-check:focus-visible + .tombol-lapor { outline: 2px solid var(--bs-primary); border-radius: .25rem; }
</style>
<script>
(() => {
    const form = document.getElementById('formPelaporan');
    const tanggal = document.getElementById('inputTanggal');
    const jam = document.getElementById('inputJam');
    const teksStatus = document.getElementById('teksStatus');
    const belum = document.getElementById('belumDisimpan');

    const LEBAR_GAMBAR = 1150; // px, lebar area saat diunduh sebagai gambar
    const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // "27 Oktober Pukul 12:00 WITA", ikut berubah saat tanggal/jam diubah
    const perbaruiTeks = () => {
        if (!tanggal.value) { teksStatus.textContent = '-'; return; }
        const [, b, h] = tanggal.value.split('-');
        teksStatus.textContent = h + ' ' + BULAN[+b - 1] + (jam.value ? ' Pukul ' + jam.value : '') + ' WITA';
    };

    document.getElementById('btnSekarang').addEventListener('click', () => {
        const wita = new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Makassar' }));
        const dua = (n) => String(n).padStart(2, '0');
        tanggal.value = wita.getFullYear() + '-' + dua(wita.getMonth() + 1) + '-' + dua(wita.getDate());
        jam.value = dua(wita.getHours()) + ':' + dua(wita.getMinutes());
        perbaruiTeks();
        belum.classList.remove('d-none');
    });

    form.addEventListener('input', () => { perbaruiTeks(); belum.classList.remove('d-none'); });

    // Jenis pelaporan dicentang → otomatis "Sudah Melaporkan"; status dibatalkan → jenis dikosongkan
    document.querySelectorAll('.cek-jenis').forEach(cb => cb.addEventListener('change', () => {
        if (cb.checked) document.getElementById('st_' + cb.dataset.baris).checked = true;
    }));
    document.querySelectorAll('.cek-status').forEach(cb => cb.addEventListener('change', () => {
        if (!cb.checked) {
            document.querySelectorAll('.cek-jenis[data-baris="' + cb.dataset.baris + '"]').forEach(j => j.checked = false);
        }
    }));

    // Unduh tabel sebagai gambar. html2canvas tetap menggambar checkbox .btn-check yang
    // tersembunyi, jadi di salinan untuk gambar checkbox & kotak samar disembunyikan.
    document.getElementById('btnUnduh').addEventListener('click', (ev) => {
        const btn = ev.currentTarget;
        const area = document.getElementById('areaLaporan');
        btn.disabled = true;
        html2canvas(area, {
            scale: 2,
            backgroundColor: '#ffffff',
            // Lebar tetap supaya nama/jabatan tidak terlipat walau layar sempit
            windowWidth: Math.max(window.innerWidth, LEBAR_GAMBAR + 200),
            onclone: (doc) => {
                const salinan = doc.getElementById('areaLaporan');
                salinan.style.width = LEBAR_GAMBAR + 'px';
                salinan.style.minWidth = LEBAR_GAMBAR + 'px';
                salinan.querySelectorAll('td, th').forEach(el => el.style.whiteSpace = 'nowrap');
                salinan.querySelectorAll('.btn-check').forEach(el => el.style.display = 'none');
                salinan.querySelectorAll('.ikon-kosong').forEach(el => el.style.visibility = 'hidden');

                // html2canvas menggambar garis menembus sel <th rowspan>; ganti dengan dua sel
                // biasa tanpa garis pemisah (judul di sel bawah), mirip sel gabungan.
                const [atas, bawah] = salinan.querySelectorAll('thead tr');
                [...atas.querySelectorAll('th[rowspan]')].reverse().forEach(th => {
                    const sel = doc.createElement('th');
                    sel.className = th.className;
                    sel.style.cssText = th.style.cssText;
                    sel.style.setProperty('border-top', '0', 'important');
                    sel.textContent = th.textContent;
                    th.removeAttribute('rowspan');
                    th.textContent = '';
                    th.style.setProperty('border-bottom', '0', 'important');
                    bawah.insertBefore(sel, bawah.firstChild);
                });
            },
        }).then((canvas) => new Promise((resolve) => {
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = 'pelaporan-kekeringan-' + (tanggal.value || 'tanpa-tanggal') + '.png';
                link.href = url;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                resolve();
            }, 'image/png');
        })).finally(() => { btn.disabled = false; });
    });
})();
</script>
@endpush

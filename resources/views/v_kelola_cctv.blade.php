@extends('layout.v_layout')
@section('title', 'Kelola CCTV')
@section('content')
@php
    $singkatan = collect(\App\Support\Satker::groups())->map(fn ($g) => $g['singkatan']);
    $dipakai = $cctvs->pluck('nama', 'device_id');
@endphp

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Kelola CCTV</h3>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('cctv.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-camera-video"></i> Lihat CCTV
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle"></i> Tambah Kamera
                </button>
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

        @if ($tuyaError)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Daftar kamera dari Tuya tidak bisa dibaca ({{ $tuyaError }}). Device ID bisa diketik manual.
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width:980px">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px" class="text-center">No.</th>
                                <th style="min-width:180px">Nama Kamera</th>
                                <th style="min-width:300px">Paket</th>
                                <th style="min-width:170px">PPK</th>
                                <th style="min-width:140px">Status</th>
                                <th style="width:200px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cctvs as $i => $c)
                                @php $online = $perangkat[$c->device_id]['online'] ?? null; @endphp
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $c->nama }}</div>
                                        @if ($c->lokasi)<div class="small text-secondary"><i class="bi bi-geo-alt"></i> {{ $c->lokasi }}</div>@endif
                                        <div class="small text-secondary">{{ $perangkat[$c->device_id]['nama'] ?? '' }} <code>{{ $c->device_id }}</code></div>
                                    </td>
                                    <td>
                                        @if ($c->packet)
                                            <span class="badge text-bg-secondary">{{ $singkatan[$c->packet->satker_group] ?? $c->packet->satker_group }}</span>
                                            <span class="small">{{ $c->kdpaket }}</span>
                                            <div class="small">{{ $c->packet->nmpaket }}</div>
                                        @else
                                            <span class="small">{{ $c->kdpaket }}</span>
                                            <div class="small text-danger">Paket tidak ada lagi di data API — pindahkan kamera ke paket lain.</div>
                                        @endif
                                    </td>
                                    <td>{{ $c->packet?->ppk?->jabatan ?? '-' }}</td>
                                    <td>
                                        @if (! $c->aktif)
                                            <span class="badge text-bg-secondary">Nonaktif</span>
                                        @elseif ($perangkat !== null && ! isset($perangkat[$c->device_id]))
                                            <span class="badge text-bg-warning" title="Kamera tidak lagi tertaut ke proyek Tuya">Tidak tertaut</span>
                                        @elseif ($online === true)
                                            <span class="badge text-bg-success">Online</span>
                                        @elseif ($online === false)
                                            <span class="badge text-bg-danger">Offline</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning text-white"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="{{ $c->id }}" data-nama="{{ $c->nama }}"
                                                data-device="{{ $c->device_id }}" data-paket="{{ $c->kdpaket }}"
                                                data-lokasi="{{ $c->lokasi }}" data-keterangan="{{ $c->keterangan }}"
                                                data-aktif="{{ $c->aktif ? 1 : 0 }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                data-id="{{ $c->id }}" data-nama="{{ $c->nama }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        Belum ada kamera. Klik <strong>Tambah Kamera</strong> untuk memasang kamera ke paket.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Daftar paket untuk kolom "Paket" (bisa diketik untuk mencari kode / nama paket) --}}
<datalist id="daftarPaket">
    @foreach ($paket as $p)
        <option value="{{ $p->kdpaket }}">{{ ($singkatan[$p->satker_group] ?? '') . ' · ' . \Illuminate\Support\Str::limit($p->nmpaket, 90) . ($p->ppk ? ' · ' . $p->ppk->jabatan : '') }}</option>
    @endforeach
</datalist>

@foreach (['Tambah' => ['primary', route('kelola-cctv.store')], 'Edit' => ['warning', null]] as $jenis => [$warna, $aksi])
<div class="modal fade" id="modal{{ $jenis }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" @if ($aksi) action="{{ $aksi }}" @endif id="form{{ $jenis }}" class="modal-content">
            @csrf
            @if ($jenis === 'Edit') @method('PUT') @endif
            <div class="modal-header text-bg-{{ $warna }}">
                <h5 class="modal-title">{{ $jenis === 'Tambah' ? 'Tambah Kamera' : 'Perbarui / Pindahkan Kamera' }}</h5>
                <button type="button" class="btn-close {{ $jenis === 'Tambah' ? 'btn-close-white' : '' }}" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kamera</label>
                    <input type="text" name="nama" class="form-control" required maxlength="255" placeholder="mis. CCTV Bendung Wawotobi 1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kamera (perangkat Tuya)</label>
                    @if ($perangkat !== null)
                        <select name="device_id" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Kamera --</option>
                            @foreach ($perangkat as $d)
                                <option value="{{ $d['id'] }}" data-dipakai="{{ $dipakai[$d['id']] ?? '' }}">
                                    {{ $d['nama'] }} ({{ $d['id'] }}){{ $d['online'] ? '' : ' — offline' }}{{ isset($dipakai[$d['id']]) ? ' — terpasang: ' . $dipakai[$d['id']] : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if (empty($perangkat))
                            <div class="form-text text-danger">Belum ada kamera yang tertaut ke proyek Tuya.</div>
                        @endif
                    @else
                        <input type="text" name="device_id" class="form-control" required maxlength="64" placeholder="Device ID dari Tuya">
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label">Paket</label>
                    <input type="text" name="kdpaket" class="form-control" list="daftarPaket" required autocomplete="off"
                           placeholder="Ketik kode atau nama paket, lalu pilih">
                    <div class="form-text info-paket">PPK kamera mengikuti PPK paket yang dipilih.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi <span class="text-secondary">(opsional)</span></label>
                        <input type="text" name="lokasi" class="form-control" maxlength="255" placeholder="mis. Hulu bendung, sisi kiri">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="aktif" value="1" class="form-check-input" id="aktif{{ $jenis }}" checked>
                            <label class="form-check-label" for="aktif{{ $jenis }}">Aktif (tampil di halaman CCTV)</label>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan <span class="text-secondary">(opsional)</span></label>
                    <textarea name="keterangan" class="form-control" rows="2" maxlength="2000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-{{ $warna }} {{ $jenis === 'Edit' ? 'text-white' : '' }}">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ============ Modal Hapus ============ --}}
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formHapus" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header text-bg-danger">
                <h5 class="modal-title" id="hapusJudul">Hapus Kamera</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Apakah anda yakin ingin menghapus kamera ini dari aplikasi? Kamera tetap ada di aplikasi Smart Life.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="submit" class="btn btn-danger">Ya</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const base = @json(url('kelola-cctv'));

        // Keterangan paket terpilih (satker · nama · PPK) dari datalist
        const labelPaket = {};
        document.querySelectorAll('#daftarPaket option').forEach(o => labelPaket[o.value] = o.textContent);
        document.querySelectorAll('[name=kdpaket]').forEach(input => {
            const info = input.closest('.mb-3').querySelector('.info-paket');
            const bawaan = info.textContent;
            const tampil = () => {
                const l = labelPaket[input.value.trim()];
                info.textContent = l || (input.value.trim() ? 'Kode paket tidak dikenal.' : bawaan);
                info.classList.toggle('text-danger', !l && input.value.trim() !== '');
            };
            input.addEventListener('input', tampil);
            input.addEventListener('change', tampil);
            input._tampil = tampil;
        });

        const modalTambah = document.getElementById('modalTambah');
        modalTambah.addEventListener('hidden.bs.modal', () => {
            document.getElementById('formTambah').reset();
            document.querySelector('#formTambah [name=kdpaket]')._tampil();
        });

        // Isi modal Perbarui dari tombol yang diklik
        document.getElementById('modalEdit').addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            const f = document.getElementById('formEdit');
            f.action = base + '/' + b.dataset.id;
            f.nama.value = b.dataset.nama || '';
            f.device_id.value = b.dataset.device || '';
            f.kdpaket.value = b.dataset.paket || '';
            f.lokasi.value = b.dataset.lokasi || '';
            f.keterangan.value = b.dataset.keterangan || '';
            f.aktif.checked = b.dataset.aktif === '1';
            f.kdpaket._tampil();
        });

        // Isi modal Hapus dari tombol yang diklik
        document.getElementById('modalHapus').addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formHapus').action = base + '/' + b.dataset.id;
            document.getElementById('hapusJudul').textContent = 'Hapus ' + (b.dataset.nama || 'Kamera');
        });
    })();
</script>
@endpush

@endsection

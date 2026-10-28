@extends('layout.v_layout')
@section('title', 'Kelola CCTV Pekerjaan')
@section('content')
@php
    $singkatan = collect(\App\Support\Satker::groups())->map(fn ($g) => $g['singkatan']);

    // Pilihan paket per PPK untuk modal (dropdown Paket mengikuti PPK yang dipilih)
    $opsiPaket = [];
    foreach ($kelompok as $daftar) {
        foreach ($daftar as $k) {
            $opsiPaket[$k['ppk']->id] = $k['paket']->map(fn ($p) => [
                $p->kdpaket,
                $p->kdpaket . ' · ' . \Illuminate\Support\Str::limit($p->nmpaket, 80),
            ])->values();
        }
    }
@endphp

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Kelola CCTV Pekerjaan</h3>
                <small class="text-secondary">Pemetaan kamera per PPK dan paket pekerjaan. Akun PPK hanya melihat kamera di paket PPK-nya sendiri.</small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('cctv.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-camera-video"></i> Lihat CCTV
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

        @if ($tuyaError)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Daftar kamera dari Tuya tidak bisa dibaca ({{ $tuyaError }}). Device ID bisa diketik manual.
            </div>
        @endif

        {{-- Kamera yang belum terlihat oleh akun PPK mana pun --}}
        @foreach (['Kamera di paket yang belum ada PPK' => $tanpaPpk, 'Kamera di paket yang sudah tidak ada di data API' => $paketHilang] as $judulPeringatan => $daftarKamera)
            @if ($daftarKamera->isNotEmpty())
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>{{ $judulPeringatan }} — belum bisa dilihat akun PPK mana pun:</div>
                    <ul class="mb-0">
                        @foreach ($daftarKamera as $c)
                            <li class="mb-1">
                                <strong>{{ $c->nama }}</strong> · {{ $c->kdpaket }}{{ $c->packet ? ' · ' . \Illuminate\Support\Str::limit($c->packet->nmpaket, 70) : '' }}
                                <button type="button" class="btn btn-sm btn-warning text-white py-0 ms-1"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                        data-id="{{ $c->id }}" data-nama="{{ $c->nama }}"
                                        data-device="{{ $c->device_id }}" data-ppk="" data-paket=""
                                        data-lokasi="{{ $c->lokasi }}" data-keterangan="{{ $c->keterangan }}"
                                        data-aktif="{{ $c->aktif ? 1 : 0 }}">
                                    Pindahkan
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    @if ($daftarKamera === $tanpaPpk)
                        <div class="small mt-1">Atau tetapkan PPK paketnya di halaman satker (Paket per Satker).</div>
                    @endif
                </div>
            @endif
        @endforeach

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="cariCctv" class="form-control" placeholder="Cari PPK, pejabat, paket, atau kamera…" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterSatker" class="form-select">
                            <option value="">Semua Satker</option>
                            @foreach ($namaSatker as $slug => $nama)
                                <option value="{{ $slug }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="hanyaBerkamera">
                            <label class="form-check-label" for="hanyaBerkamera">Hanya PPK yang sudah punya kamera</label>
                        </div>
                    </div>
                </div>
                <div class="small text-secondary mt-1">Total {{ $jumlahKamera }} kamera terpasang.</div>
            </div>
        </div>

        @foreach ($kelompok as $slug => $daftar)
            <div class="seksi-satker mb-4" data-satker="{{ $slug }}">
                <h5 class="mb-2">{{ $namaSatker[$slug] ?? $slug }}</h5>

                @forelse ($daftar as $k)
                    @php
                        $ppk = $k['ppk'];
                        $paketBerkamera = $k['paket']->filter(fn ($p) => isset($kameraPerPaket[$p->kdpaket]));
                        $cari = collect([$ppk->jabatan, $ppk->nama])
                            ->merge($paketBerkamera->flatMap(fn ($p) => [$p->kdpaket, $p->nmpaket]))
                            ->merge($paketBerkamera->flatMap(fn ($p) => $kameraPerPaket[$p->kdpaket]->pluck('nama')))
                            ->implode(' ');
                    @endphp
                    <div class="card mb-2 kartu-ppk" data-cari="{{ \Illuminate\Support\Str::lower($cari) }}" data-kamera="{{ $k['jumlahKamera'] }}">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2">
                            <div class="me-auto">
                                <div class="fw-semibold">{{ $ppk->jabatan }}</div>
                                <div class="small text-secondary">{{ $ppk->nama ?: '—' }} · {{ $k['paket']->count() }} paket</div>
                            </div>
                            <span class="badge {{ $k['jumlahKamera'] ? 'text-bg-info' : 'text-bg-secondary' }}">
                                <i class="bi bi-camera-video"></i> {{ $k['jumlahKamera'] }} kamera
                            </span>
                            @forelse ($k['akun'] as $u)
                                <span class="badge text-bg-success" title="Akun PPK: {{ $u->name }}">
                                    <i class="bi bi-person-check"></i> {{ $u->email }}
                                </span>
                            @empty
                                <a href="{{ route('users.index') }}" class="badge text-bg-warning text-decoration-none"
                                   title="Buat akun role PPK untuk PPK ini di Kelola Akun">
                                    <i class="bi bi-person-x"></i> Belum ada akun PPK
                                </a>
                            @endforelse
                            <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalTambah" data-ppk="{{ $ppk->id }}"
                                    @disabled($k['paket']->isEmpty())
                                    title="{{ $k['paket']->isEmpty() ? 'PPK ini belum punya paket' : '' }}">
                                <i class="bi bi-plus-circle"></i> Tambah Kamera
                            </button>
                        </div>
                        @if ($paketBerkamera->isNotEmpty())
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" style="min-width:820px">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width:300px">Paket</th>
                                                <th style="min-width:220px">Kamera</th>
                                                <th style="width:120px">Status</th>
                                                <th style="width:190px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($paketBerkamera as $p)
                                                @foreach ($kameraPerPaket[$p->kdpaket] as $j => $c)
                                                    @php $online = $perangkat[$c->device_id]['online'] ?? null; @endphp
                                                    <tr>
                                                        @if ($j === 0)
                                                            <td rowspan="{{ $kameraPerPaket[$p->kdpaket]->count() }}">
                                                                <span class="small">{{ $p->kdpaket }}</span>
                                                                <div class="small">{{ $p->nmpaket }}</div>
                                                            </td>
                                                        @endif
                                                        <td>
                                                            <div class="fw-semibold">{{ $c->nama }}</div>
                                                            @if ($c->lokasi)<div class="small text-secondary"><i class="bi bi-geo-alt"></i> {{ $c->lokasi }}</div>@endif
                                                        </td>
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
                                                                    data-device="{{ $c->device_id }}" data-ppk="{{ $ppk->id }}"
                                                                    data-paket="{{ $c->kdpaket }}"
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
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-secondary small mb-2">Belum ada PPK di satker ini (tambahkan di Kelola PPK).</div>
                @endforelse
            </div>
        @endforeach

        <div id="barisKosong" class="text-center text-secondary py-4 d-none">Tidak ada PPK yang cocok dengan pencarian.</div>

    </div>
</div>

@foreach (['Tambah' => ['primary', route('kelola-cctv-pekerjaan.store')], 'Edit' => ['warning', null]] as $jenis => [$warna, $aksi])
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
                                <option value="{{ $d['id'] }}">
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
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">PPK</label>
                        <select name="ppk_id" class="form-select" required>
                            <option value="" disabled selected>-- Pilih PPK --</option>
                            @foreach ($kelompok as $slug => $daftar)
                                @if ($daftar->isNotEmpty())
                                    <optgroup label="{{ $singkatan[$slug] ?? $slug }}">
                                        @foreach ($daftar as $k)
                                            <option value="{{ $k['ppk']->id }}">{{ $k['ppk']->jabatan }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="form-label">Paket Pekerjaan</label>
                        <select name="kdpaket" class="form-select" required>
                            <option value="" disabled selected>-- Pilih PPK dulu --</option>
                        </select>
                    </div>
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
        const base = @json(url('kelola-cctv-pekerjaan'));
        const opsiPaket = @json($opsiPaket);

        // Dropdown Paket hanya berisi paket milik PPK yang dipilih
        function isiPaket(form, kdpaket) {
            const pilih = form.kdpaket;
            const daftar = opsiPaket[form.ppk_id.value] || [];
            pilih.innerHTML = '';
            const kosong = new Option(form.ppk_id.value ? (daftar.length ? '-- Pilih Paket --' : 'PPK ini belum punya paket') : '-- Pilih PPK dulu --', '');
            kosong.disabled = true;
            pilih.add(kosong);
            daftar.forEach(([kd, label]) => pilih.add(new Option(label, kd)));
            pilih.value = kdpaket || '';
            if (!pilih.value) kosong.selected = true;
        }

        ['formTambah', 'formEdit'].forEach(id => {
            const f = document.getElementById(id);
            f.ppk_id.addEventListener('change', () => isiPaket(f));
        });

        // Tambah: PPK sudah terpilih sesuai kartu yang diklik
        document.getElementById('modalTambah').addEventListener('show.bs.modal', (ev) => {
            const f = document.getElementById('formTambah');
            f.reset();
            f.ppk_id.value = ev.relatedTarget?.dataset.ppk || '';
            isiPaket(f);
        });

        // Perbarui: isi dari tombol yang diklik
        document.getElementById('modalEdit').addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            const f = document.getElementById('formEdit');
            f.action = base + '/' + b.dataset.id;
            f.nama.value = b.dataset.nama || '';
            f.device_id.value = b.dataset.device || '';
            f.ppk_id.value = b.dataset.ppk || '';
            isiPaket(f, b.dataset.paket);
            f.lokasi.value = b.dataset.lokasi || '';
            f.keterangan.value = b.dataset.keterangan || '';
            f.aktif.checked = b.dataset.aktif === '1';
        });

        document.getElementById('modalHapus').addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formHapus').action = base + '/' + b.dataset.id;
            document.getElementById('hapusJudul').textContent = 'Hapus ' + (b.dataset.nama || 'Kamera');
        });

        // Filter: pencarian + satker + hanya yang berkamera (AND)
        const cari = document.getElementById('cariCctv');
        const satker = document.getElementById('filterSatker');
        const berkamera = document.getElementById('hanyaBerkamera');

        function saring() {
            const q = cari.value.trim().toLowerCase();
            let total = 0;
            document.querySelectorAll('.seksi-satker').forEach(seksi => {
                let tampil = 0;
                const satkerCocok = !satker.value || seksi.dataset.satker === satker.value;
                seksi.querySelectorAll('.kartu-ppk').forEach(k => {
                    const cocok = satkerCocok
                        && (!q || k.dataset.cari.includes(q))
                        && (!berkamera.checked || k.dataset.kamera !== '0');
                    k.classList.toggle('d-none', !cocok);
                    if (cocok) tampil++;
                });
                seksi.classList.toggle('d-none', tampil === 0);
                total += tampil;
            });
            document.getElementById('barisKosong').classList.toggle('d-none', total > 0);
        }

        cari.addEventListener('input', saring);
        satker.addEventListener('change', saring);
        berkamera.addEventListener('change', saring);
    })();
</script>
@endpush

@endsection

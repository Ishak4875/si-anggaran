@extends('layout.v_layout')
@section('title', 'Agenda Rapat')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Agenda Rapat</h3>
                <small class="text-secondary">Daftar agenda rapat, terurut otomatis berdasarkan tanggal.</small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle"></i> Tambah
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

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold">Daftar Agenda</span>
            </div>
            <div class="card-header d-flex flex-wrap align-items-center gap-2 border-top-0">
                <button type="button" id="bulanToday" class="btn btn-sm btn-outline-secondary">Today</button>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" id="bulanPrev" class="btn btn-outline-secondary" title="Bulan sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" id="bulanNext" class="btn btn-outline-secondary" title="Bulan berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <span id="bulanLabel" class="fw-semibold fs-6 ms-1"></span>
                <div class="input-group input-group-sm ms-auto" style="max-width:320px">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="cariAgenda" class="form-control"
                           placeholder="Cari tanggal / nama agenda..." autocomplete="off">
                    <button type="button" id="cariReset" class="btn btn-outline-secondary" title="Bersihkan">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width:960px">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px" class="text-center">No.</th>
                                <th style="width:130px">Tanggal</th>
                                <th style="min-width:220px">Nama Agenda</th>
                                <th style="width:100px">Waktu</th>
                                <th style="min-width:160px">Ruangan</th>
                                <th style="min-width:220px">Keterangan</th>
                                <th style="width:210px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="barisAgenda">
                            @forelse ($agendas as $i => $agenda)
                                @php
                                    $tanggalCari = $agenda->tanggal_agenda
                                        ? $agenda->tanggal_agenda->format('d/m/Y') . ' ' . $agenda->tanggal_agenda->format('d-m-Y') . ' ' . $agenda->tanggal_agenda->locale('id')->translatedFormat('d F Y')
                                        : '';
                                @endphp
                                <tr class="agenda-row"
                                    data-cari="{{ Str::lower(trim($tanggalCari . ' ' . $agenda->nama_agenda)) }}"
                                    data-tanggal-iso="{{ optional($agenda->tanggal_agenda)->format('Y-m-d') }}"
                                    data-bulan="{{ optional($agenda->tanggal_agenda)->format('Y-m') }}">
                                    <td class="text-center nomor">{{ $i + 1 }}</td>
                                    <td>{{ $agenda->tanggal_agenda?->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $agenda->nama_agenda }}</td>
                                    <td>{{ $agenda->waktu ? \Illuminate\Support\Carbon::parse($agenda->waktu)->format('H:i') : '—' }}</td>
                                    <td>{{ $agenda->ruangan ?: '—' }}</td>
                                    <td style="white-space: pre-line">{{ $agenda->keterangan ?: '—' }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning text-white btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="{{ $agenda->id }}"
                                                data-tanggal="{{ optional($agenda->tanggal_agenda)->format('Y-m-d') }}"
                                                data-nama="{{ $agenda->nama_agenda }}"
                                                data-waktu="{{ $agenda->waktu ? \Illuminate\Support\Carbon::parse($agenda->waktu)->format('H:i') : '' }}"
                                                data-ruangan="{{ $agenda->ruangan }}"
                                                data-keterangan="{{ $agenda->keterangan }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-hapus"
                                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                data-id="{{ $agenda->id }}"
                                                data-nama="{{ $agenda->nama_agenda }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="barisKosongAwal">
                                    <td colspan="7" class="text-center py-4 text-secondary">
                                        Belum ada agenda rapat. Klik <strong>Tambah</strong> untuk menambah.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="barisKosongCari" class="d-none">
                                <td colspan="7" class="text-center py-4 text-secondary">
                                    <i class="bi bi-search me-1"></i> Tidak ada agenda yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ============ Modal Tambah ============ --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('agenda-rapat.store') }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-primary">
                <h5 class="modal-title">Tambah Agenda Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal Agenda</label>
                    <input type="date" name="tanggal_agenda" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Agenda</label>
                    <input type="text" name="nama_agenda" class="form-control" required placeholder="Cth: Rapat Koordinasi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Waktu</label>
                    <input type="time" name="waktu" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <input type="text" name="ruangan" class="form-control" placeholder="Cth: Ruang Lasolo">
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan Tambahan"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ Modal Perbarui ============ --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formEdit" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header text-bg-warning">
                <h5 class="modal-title">Perbarui Agenda Rapat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal Agenda</label>
                    <input type="date" name="tanggal_agenda" id="editTanggal" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Agenda</label>
                    <input type="text" name="nama_agenda" id="editNama" class="form-control" required placeholder="Cth: Rapat Koordinasi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Waktu</label>
                    <input type="time" name="waktu" id="editWaktu" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <input type="text" name="ruangan" id="editRuangan" class="form-control" placeholder="Cth: Ruang Lasolo">
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="editKeterangan" class="form-control" rows="3" placeholder="Keterangan Tambahan"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-warning text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ Modal Hapus ============ --}}
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formHapus" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header text-bg-danger">
                <h5 class="modal-title" id="hapusJudul">Hapus Agenda Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Apakah anda yakin ingin menghapus data ini?
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
        const base = @json(url('agenda-rapat'));

        // Isi modal Perbarui dari tombol yang diklik
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formEdit').action = base + '/' + b.dataset.id;
            document.getElementById('editTanggal').value    = b.dataset.tanggal || '';
            document.getElementById('editNama').value       = b.dataset.nama || '';
            document.getElementById('editWaktu').value      = b.dataset.waktu || '';
            document.getElementById('editRuangan').value    = b.dataset.ruangan || '';
            document.getElementById('editKeterangan').value = b.dataset.keterangan || '';
        });

        // Isi modal Hapus dari tombol yang diklik
        const modalHapus = document.getElementById('modalHapus');
        modalHapus.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formHapus').action = base + '/' + b.dataset.id;
            document.getElementById('hapusJudul').textContent = b.dataset.nama || 'Hapus Agenda Rapat';
        });

        // Pencarian real-time berdasarkan tanggal / nama agenda, ditambah navigasi filter bulan
        const cariInput = document.getElementById('cariAgenda');
        const cariReset = document.getElementById('cariReset');
        const kosongCari = document.getElementById('barisKosongCari');
        const bulanLabel = document.getElementById('bulanLabel');
        const bulanPrev = document.getElementById('bulanPrev');
        const bulanNext = document.getElementById('bulanNext');
        const bulanToday = document.getElementById('bulanToday');

        const NAMA_BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const now = new Date();
        let bulanAktif = { tahun: now.getFullYear(), bulan: now.getMonth() + 1 }; // bulan: 1-12
        let modeHariIni = false; // true = "Today" aktif, filter hanya tanggal hari ini
        const todayIso = new Date().toLocaleDateString('sv-SE'); // format YYYY-MM-DD, timezone lokal browser

        function bulanKey({ tahun, bulan }) {
            return `${tahun}-${String(bulan).padStart(2, '0')}`;
        }

        function updateBulanLabel() {
            if (modeHariIni) {
                const n = new Date();
                bulanLabel.textContent = `${n.getDate()} ${NAMA_BULAN[n.getMonth()]} ${n.getFullYear()}`;
            } else {
                bulanLabel.textContent = `${NAMA_BULAN[bulanAktif.bulan - 1]} ${bulanAktif.tahun}`;
            }
        }

        function filterAgenda() {
            const q = cariInput.value.trim().toLowerCase();
            const bulanFilter = bulanKey(bulanAktif);
            const rows = Array.from(document.querySelectorAll('.agenda-row'));
            let visible = 0;

            rows.forEach((row) => {
                const cocokCari = q === '' || row.dataset.cari.includes(q);
                // Agenda tanpa tanggal selalu ikut tampil, tidak terikat bulan/hari manapun.
                const tanpaTanggal = row.dataset.tanggalIso === '';
                const cocokWaktu = tanpaTanggal || (modeHariIni
                    ? row.dataset.tanggalIso === todayIso
                    : row.dataset.bulan === bulanFilter);
                const cocok = cocokCari && cocokWaktu;

                row.classList.toggle('d-none', !cocok);
                if (cocok) {
                    visible++;
                    row.querySelector('.nomor').textContent = visible;
                }
            });

            kosongCari.classList.toggle('d-none', visible !== 0 || rows.length === 0);
        }

        function gantiBulan(selisih) {
            // Navigasi bulan selalu keluar dari mode "Today" (hari spesifik) kembali ke mode bulan.
            if (modeHariIni) {
                const n = new Date();
                bulanAktif = { tahun: n.getFullYear(), bulan: n.getMonth() + 1 };
            }
            modeHariIni = false;

            let { tahun, bulan } = bulanAktif;
            bulan += selisih;
            if (bulan > 12) { bulan = 1; tahun++; }
            if (bulan < 1) { bulan = 12; tahun--; }
            bulanAktif = { tahun, bulan };
            updateBulanLabel();
            filterAgenda();
        }

        if (cariInput) {
            cariInput.addEventListener('input', filterAgenda);
            cariReset.addEventListener('click', () => {
                cariInput.value = '';
                filterAgenda();
                cariInput.focus();
            });
            bulanPrev.addEventListener('click', () => gantiBulan(-1));
            bulanNext.addEventListener('click', () => gantiBulan(1));
            bulanToday.addEventListener('click', () => {
                modeHariIni = true;
                updateBulanLabel();
                filterAgenda();
            });

            // Default: langsung terfilter ke bulan berjalan saat halaman dibuka.
            updateBulanLabel();
            filterAgenda();
        }
    })();
</script>
@endpush

@endsection

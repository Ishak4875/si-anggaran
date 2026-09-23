@extends('layout.v_layout')
@section('title', 'PR')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Pekerjaan Rumah (PR)</h3>
                <small class="text-secondary">Daftar tindak lanjut / tugas, terurut otomatis berdasarkan deadline.</small>
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
                <span class="fw-semibold">Daftar PR</span>
                <div class="input-group input-group-sm ms-auto" style="max-width:320px">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="cariPr" class="form-control"
                           placeholder="Cari nama pekerjaan / penanggung jawab..." autocomplete="off">
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
                                <th style="min-width:220px">Nama Pekerjaan</th>
                                <th style="min-width:160px">Penanggung Jawab</th>
                                <th style="width:130px">Deadline</th>
                                <th style="width:120px">Status</th>
                                <th style="min-width:200px">Keterangan</th>
                                <th style="width:210px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="barisPr">
                            @forelse ($pekerjaans as $i => $pr)
                                <tr class="pr-row"
                                    data-cari="{{ Str::lower(trim($pr->nama_pekerjaan . ' ' . $pr->penanggung_jawab)) }}">
                                    <td class="text-center nomor">{{ $i + 1 }}</td>
                                    <td>{{ $pr->nama_pekerjaan }}</td>
                                    <td>{{ $pr->penanggung_jawab ?: '—' }}</td>
                                    <td>{{ $pr->deadline?->format('d/m/Y') ?: '—' }}</td>
                                    <td>
                                        @php
                                            $statusBadge = [
                                                'belum'   => 'text-bg-secondary',
                                                'proses'  => 'text-bg-warning',
                                                'selesai' => 'text-bg-success',
                                            ][$pr->status] ?? 'text-bg-secondary';
                                            $statusLabel = [
                                                'belum'   => 'Belum',
                                                'proses'  => 'Proses',
                                                'selesai' => 'Selesai',
                                            ][$pr->status] ?? $pr->status;
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td style="white-space: pre-line">{{ $pr->keterangan ?: '—' }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning text-white btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="{{ $pr->id }}"
                                                data-nama="{{ $pr->nama_pekerjaan }}"
                                                data-penanggung="{{ $pr->penanggung_jawab }}"
                                                data-deadline="{{ optional($pr->deadline)->format('Y-m-d') }}"
                                                data-status="{{ $pr->status }}"
                                                data-keterangan="{{ $pr->keterangan }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-hapus"
                                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                data-id="{{ $pr->id }}"
                                                data-nama="{{ $pr->nama_pekerjaan }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="barisKosongAwal">
                                    <td colspan="7" class="text-center py-4 text-secondary">
                                        Belum ada PR. Klik <strong>Tambah</strong> untuk menambah.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="barisKosongCari" class="d-none">
                                <td colspan="7" class="text-center py-4 text-secondary">
                                    <i class="bi bi-search me-1"></i> Tidak ada PR yang cocok dengan pencarian.
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
        <form method="POST" action="{{ route('pr.store') }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-primary">
                <h5 class="modal-title">Tambah PR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Pekerjaan</label>
                    <input type="text" name="nama_pekerjaan" class="form-control" required placeholder="Cth: Kirim laporan revisi ke Ditjen SDA">
                </div>
                <div class="mb-3">
                    <label class="form-label">Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" class="form-control" placeholder="Cth: PPK Atab I">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="belum" selected>Belum</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
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
                <h5 class="modal-title">Perbarui PR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Pekerjaan</label>
                    <input type="text" name="nama_pekerjaan" id="editNama" class="form-control" required placeholder="Cth: Kirim laporan revisi ke Ditjen SDA">
                </div>
                <div class="mb-3">
                    <label class="form-label">Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" id="editPenanggung" class="form-control" placeholder="Cth: PPK Atab I">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" id="editDeadline" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="editStatus" class="form-select" required>
                        <option value="belum">Belum</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
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
                <h5 class="modal-title" id="hapusJudul">Hapus PR</h5>
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
        const base = @json(url('pr'));

        // Isi modal Perbarui dari tombol yang diklik
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formEdit').action = base + '/' + b.dataset.id;
            document.getElementById('editNama').value       = b.dataset.nama || '';
            document.getElementById('editPenanggung').value = b.dataset.penanggung || '';
            document.getElementById('editDeadline').value   = b.dataset.deadline || '';
            document.getElementById('editStatus').value     = b.dataset.status || 'belum';
            document.getElementById('editKeterangan').value = b.dataset.keterangan || '';
        });

        // Isi modal Hapus dari tombol yang diklik
        const modalHapus = document.getElementById('modalHapus');
        modalHapus.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formHapus').action = base + '/' + b.dataset.id;
            document.getElementById('hapusJudul').textContent = b.dataset.nama || 'Hapus PR';
        });

        // Pencarian real-time berdasarkan nama pekerjaan / penanggung jawab
        const cariInput = document.getElementById('cariPr');
        const cariReset = document.getElementById('cariReset');
        const kosongCari = document.getElementById('barisKosongCari');

        function filterPr() {
            const q = cariInput.value.trim().toLowerCase();
            const rows = Array.from(document.querySelectorAll('.pr-row'));
            let visible = 0;

            rows.forEach((row) => {
                const cocok = q === '' || row.dataset.cari.includes(q);
                row.classList.toggle('d-none', !cocok);
                if (cocok) {
                    visible++;
                    row.querySelector('.nomor').textContent = visible;
                }
            });

            kosongCari.classList.toggle('d-none', visible !== 0 || rows.length === 0);
        }

        if (cariInput) {
            cariInput.addEventListener('input', filterPr);
            cariReset.addEventListener('click', () => {
                cariInput.value = '';
                filterPr();
                cariInput.focus();
            });
        }
    })();
</script>
@endpush

@endsection

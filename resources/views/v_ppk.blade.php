@extends('layout.v_layout')
@section('title', 'Kelola PPK')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">PPK</h3>
                <small class="text-secondary">Daftar Pejabat Pembuat Komitmen (PPK) per Satker.</small>
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width:820px">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px" class="text-center">No.</th>
                                <th style="min-width:230px">PPK (Jabatan)</th>
                                <th style="min-width:220px">Satker</th>
                                <th style="min-width:240px">Nama PPK Saat Ini</th>
                                <th style="width:210px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ppks as $i => $ppk)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $ppk->jabatan }}</td>
                                    <td>{{ $ppk->satker }}</td>
                                    <td>{{ $ppk->nama ?: '—' }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning text-white btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="{{ $ppk->id }}"
                                                data-jabatan="{{ $ppk->jabatan }}"
                                                data-satker="{{ $ppk->satker }}"
                                                data-nama="{{ $ppk->nama }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-hapus"
                                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                data-id="{{ $ppk->id }}"
                                                data-jabatan="{{ $ppk->jabatan }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">
                                        Belum ada data PPK. Klik <strong>Tambah</strong> untuk menambah.
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

{{-- ============ Modal Tambah ============ --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('ppk.store') }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-primary">
                <h5 class="modal-title">Tambah PPK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Jabatan (PPK)</label>
                    <input type="text" name="jabatan" class="form-control" required placeholder="mis. PPK Irigasi dan Rawa I">
                </div>
                <div class="mb-3">
                    <label class="form-label">Satker</label>
                    <select name="satker" class="form-select" required>
                        <option value="" disabled selected>-- Pilih Satker --</option>
                        @foreach ($satkerOptions as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Nama PPK Saat Ini</label>
                    <input type="text" name="nama" class="form-control" placeholder="mis. Ir. Nama Lengkap, S.T.">
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
                <h5 class="modal-title">Perbarui PPK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Jabatan (PPK)</label>
                    <input type="text" name="jabatan" id="editJabatan" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Satker</label>
                    <select name="satker" id="editSatker" class="form-select" required>
                        <option value="" disabled>-- Pilih Satker --</option>
                        @foreach ($satkerOptions as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Nama PPK Saat Ini</label>
                    <input type="text" name="nama" id="editNama" class="form-control">
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
                <h5 class="modal-title" id="hapusJudul">Hapus PPK</h5>
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
        const base = @json(url('ppk'));

        // Isi modal Perbarui dari tombol yang diklik
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formEdit').action = base + '/' + b.dataset.id;
            document.getElementById('editJabatan').value = b.dataset.jabatan || '';
            document.getElementById('editSatker').value  = b.dataset.satker || '';
            document.getElementById('editNama').value    = b.dataset.nama || '';
        });

        // Isi modal Hapus dari tombol yang diklik
        const modalHapus = document.getElementById('modalHapus');
        modalHapus.addEventListener('show.bs.modal', (ev) => {
            const b = ev.relatedTarget;
            document.getElementById('formHapus').action = base + '/' + b.dataset.id;
            document.getElementById('hapusJudul').textContent = b.dataset.jabatan || 'Hapus PPK';
        });
    })();
</script>
@endpush

@endsection

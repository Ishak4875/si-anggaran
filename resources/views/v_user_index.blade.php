@extends('layout.v_layout')
@section('title', 'Kelola Akun')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Kelola Akun</h3>
                <small class="text-secondary">Daftar akun pengguna dan kelola hak akses.</small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle"></i> Tambah Akun
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width:820px">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px" class="text-center">No.</th>
                                <th style="min-width:200px">Nama</th>
                                <th style="min-width:220px">Email</th>
                                <th style="min-width:120px">Role</th>
                                <th style="min-width:180px">Terdaftar</th>
                                <th style="width:210px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $i => $user)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->isAdmin())
                                            <span class="badge bg-danger">Super Admin</span>
                                        @else
                                            <span class="badge bg-secondary">User</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning text-white btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                data-role="{{ $user->role }}">
                                            <i class="bi bi-pencil-square"></i> Perbarui
                                        </button>
                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                    class="btn btn-sm btn-danger btn-hapus"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        Belum ada akun. Klik <strong>Tambah Akun</strong> untuk menambah.
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
        <form method="POST" action="{{ route('users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header text-bg-primary">
                <h5 class="modal-title">Tambah Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" required placeholder="Nama lengkap pengguna">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                </div>
                <div class="mb-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="" disabled selected>-- Pilih Role --</option>
                        <option value="user">User</option>
                        <option value="admin">Super Admin</option>
                    </select>
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
                <h5 class="modal-title">Perbarui Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="editEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" id="editPassword" class="form-control" placeholder="Masukkan password baru (opsional)">
                </div>
                <div class="mb-2">
                    <label class="form-label">Role</label>
                    <select name="role" id="editRole" class="form-select" required>
                        <option value="user">User</option>
                        <option value="admin">Super Admin</option>
                    </select>
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
                <h5 class="modal-title" id="hapusJudul">Hapus Akun</h5>
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
    document.addEventListener('DOMContentLoaded', () => {
        const base = @json(url('users'));

        // Reset modal Tambah saat ditutup
        const modalTambah = document.getElementById('modalTambah');
        if (modalTambah) {
            modalTambah.addEventListener('hidden.bs.modal', () => {
                document.querySelector('#modalTambah form').reset();
            });
        }

        // Isi modal Perbarui dari tombol yang diklik
        const modalEdit = document.getElementById('modalEdit');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', (ev) => {
                const b = ev.relatedTarget;
                document.getElementById('formEdit').action = base + '/' + b.dataset.id;
                document.getElementById('editName').value = b.dataset.name || '';
                document.getElementById('editEmail').value = b.dataset.email || '';
                document.getElementById('editRole').value = b.dataset.role || '';
                document.getElementById('editPassword').value = '';
            });
        }

        // Isi modal Hapus dari tombol yang diklik
        const modalHapus = document.getElementById('modalHapus');
        if (modalHapus) {
            modalHapus.addEventListener('show.bs.modal', (ev) => {
                const b = ev.relatedTarget;
                document.getElementById('formHapus').action = base + '/' + b.dataset.id;
                document.getElementById('hapusJudul').textContent = b.dataset.name || 'Pengguna';
            });
        }
    });
</script>
@endpush

@endsection

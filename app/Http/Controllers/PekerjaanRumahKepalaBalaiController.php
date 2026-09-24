<?php

namespace App\Http\Controllers;

use App\Models\PekerjaanRumah;
use App\Models\PekerjaanRumahKepalaBalai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PekerjaanRumahKepalaBalaiController extends Controller
{
    /**
     * Aturan validasi untuk tambah/ubah PR Kepala Balai.
     */
    private function rules(): array
    {
        return [
            'nama_pekerjaan'   => ['required', 'string', 'max:255'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'deadline'         => ['nullable', 'date'],
            'status'           => ['required', 'in:' . implode(',', PekerjaanRumah::STATUS_OPTIONS)],
            'keterangan'       => ['nullable', 'string'],
        ];
    }

    /**
     * Halaman daftar PR Kepala Balai (memakai view yang sama dengan PR KPISDA).
     */
    public function index(): View
    {
        return view('v_pekerjaan_rumah', [
            'judul'       => 'PR Kepala Balai',
            'routePrefix' => 'pr-kepala-balai',
            // Deadline terdekat dulu; baris tanpa deadline (opsional) tetap di akhir.
            'pekerjaans' => PekerjaanRumahKepalaBalai::orderByRaw('deadline IS NULL')
                ->orderBy('deadline')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PekerjaanRumahKepalaBalai::create($request->validate($this->rules()));

        return redirect()
            ->route('pr-kepala-balai.index')
            ->with('status', 'PR Kepala Balai berhasil ditambahkan.');
    }

    public function update(Request $request, PekerjaanRumahKepalaBalai $pekerjaanRumahKepalaBalai): RedirectResponse
    {
        $pekerjaanRumahKepalaBalai->update($request->validate($this->rules()));

        return redirect()
            ->route('pr-kepala-balai.index')
            ->with('status', 'PR Kepala Balai berhasil diperbarui.');
    }

    public function destroy(PekerjaanRumahKepalaBalai $pekerjaanRumahKepalaBalai): RedirectResponse
    {
        $pekerjaanRumahKepalaBalai->delete();

        return redirect()
            ->route('pr-kepala-balai.index')
            ->with('status', 'PR Kepala Balai berhasil dihapus.');
    }
}

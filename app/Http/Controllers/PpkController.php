<?php

namespace App\Http\Controllers;

use App\Models\Ppk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PpkController extends Controller
{
    /**
     * Aturan validasi untuk tambah/ubah PPK.
     */
    private function rules(): array
    {
        return [
            'jabatan' => ['required', 'string', 'max:255'],
            'satker'  => ['required', 'string', Rule::in(Ppk::SATKER_OPTIONS)],
            'nama'    => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Halaman daftar PPK (tabel + modal tambah/ubah/hapus).
     */
    public function index(): View
    {
        return view('v_ppk', [
            'ppks'          => Ppk::orderBy('urutan')->orderBy('id')->get(),
            'satkerOptions' => Ppk::SATKER_OPTIONS,
        ]);
    }

    /**
     * Tambah satu PPK.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $data['urutan'] = (int) Ppk::max('urutan') + 1;
        $data['nama']   = $data['nama'] ?: null;

        Ppk::create($data);

        return redirect()
            ->route('ppk.index')
            ->with('status', 'Data PPK berhasil ditambahkan.');
    }

    /**
     * Perbarui satu PPK.
     */
    public function update(Request $request, Ppk $ppk): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['nama'] = $data['nama'] ?: null;

        $ppk->update($data);

        return redirect()
            ->route('ppk.index')
            ->with('status', 'Data PPK berhasil diperbarui.');
    }

    /**
     * Hapus satu PPK.
     */
    public function destroy(Ppk $ppk): RedirectResponse
    {
        $ppk->delete();

        return redirect()
            ->route('ppk.index')
            ->with('status', 'Data PPK berhasil dihapus.');
    }
}

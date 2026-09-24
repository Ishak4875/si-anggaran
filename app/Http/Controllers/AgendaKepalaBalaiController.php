<?php

namespace App\Http\Controllers;

use App\Models\AgendaKepalaBalai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaKepalaBalaiController extends Controller
{
    /**
     * Aturan validasi untuk tambah/ubah agenda Kepala Balai.
     */
    private function rules(): array
    {
        return [
            'tanggal_agenda' => ['nullable', 'date'],
            'nama_agenda'    => ['required', 'string', 'max:255'],
            'waktu'          => ['nullable'],
            'ruangan'        => ['nullable', 'string', 'max:255'],
            'keterangan'     => ['nullable', 'string'],
        ];
    }

    /**
     * Halaman daftar agenda Kepala Balai (memakai view yang sama dengan Agenda Rapat).
     */
    public function index(): View
    {
        return view('v_agenda_rapat', [
            'judul'       => 'Agenda Rapat Kepala Balai',
            'routePrefix' => 'agenda-kepala-balai',
            // Terbaru dulu (descending); baris tanpa tanggal/waktu (opsional) tetap di akhir.
            'agendas' => AgendaKepalaBalai::orderByRaw('tanggal_agenda IS NULL')
                ->orderByDesc('tanggal_agenda')
                ->orderByRaw('waktu IS NULL')
                ->orderByDesc('waktu')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AgendaKepalaBalai::create($request->validate($this->rules()));

        return redirect()
            ->route('agenda-kepala-balai.index')
            ->with('status', 'Agenda Kepala Balai berhasil ditambahkan.');
    }

    public function update(Request $request, AgendaKepalaBalai $agendaKepalaBalai): RedirectResponse
    {
        $agendaKepalaBalai->update($request->validate($this->rules()));

        return redirect()
            ->route('agenda-kepala-balai.index')
            ->with('status', 'Agenda Kepala Balai berhasil diperbarui.');
    }

    public function destroy(AgendaKepalaBalai $agendaKepalaBalai): RedirectResponse
    {
        $agendaKepalaBalai->delete();

        return redirect()
            ->route('agenda-kepala-balai.index')
            ->with('status', 'Agenda Kepala Balai berhasil dihapus.');
    }
}

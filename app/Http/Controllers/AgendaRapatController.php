<?php

namespace App\Http\Controllers;

use App\Models\AgendaRapat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaRapatController extends Controller
{
    /**
     * Aturan validasi untuk tambah/ubah agenda rapat.
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
     * Halaman daftar agenda rapat (tabel + modal tambah/ubah/hapus).
     */
    public function index(): View
    {
        return view('v_agenda_rapat', [
            // Baris tanpa tanggal/waktu (opsional) ditempatkan di akhir.
            'agendas' => AgendaRapat::orderByRaw('tanggal_agenda IS NULL')
                ->orderBy('tanggal_agenda')
                ->orderByRaw('waktu IS NULL')
                ->orderBy('waktu')
                ->get(),
        ]);
    }

    /**
     * Tambah satu agenda rapat.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        AgendaRapat::create($data);

        return redirect()
            ->route('agenda-rapat.index')
            ->with('status', 'Agenda rapat berhasil ditambahkan.');
    }

    /**
     * Perbarui satu agenda rapat.
     */
    public function update(Request $request, AgendaRapat $agendaRapat): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $agendaRapat->update($data);

        return redirect()
            ->route('agenda-rapat.index')
            ->with('status', 'Agenda rapat berhasil diperbarui.');
    }

    /**
     * Hapus satu agenda rapat.
     */
    public function destroy(AgendaRapat $agendaRapat): RedirectResponse
    {
        $agendaRapat->delete();

        return redirect()
            ->route('agenda-rapat.index')
            ->with('status', 'Agenda rapat berhasil dihapus.');
    }
}

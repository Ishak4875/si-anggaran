<?php

namespace App\Http\Controllers;

use App\Models\AgendaRapat;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaRapatController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendar)
    {
    }

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
        // Terbaru dulu (descending); baris tanpa tanggal/waktu (opsional) tetap di akhir.
        $agendas = AgendaRapat::orderByRaw('tanggal_agenda IS NULL')
            ->orderByDesc('tanggal_agenda')
            ->orderByRaw('waktu IS NULL')
            ->orderByDesc('waktu')
            ->get();

        // Opsi dropdown filter bulan: unique bulan-tahun dari data yang ada, terbaru dulu.
        $bulanOptions = $agendas
            ->whereNotNull('tanggal_agenda')
            ->map(fn ($a) => $a->tanggal_agenda->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->mapWithKeys(fn ($ym) => [$ym => \Illuminate\Support\Carbon::createFromFormat('Y-m', $ym)->locale('id')->translatedFormat('F Y')]);

        return view('v_agenda_rapat', [
            'agendas'      => $agendas,
            'bulanOptions' => $bulanOptions,
        ]);
    }

    /**
     * Tambah satu agenda rapat.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $agenda = AgendaRapat::create($data);
        $this->googleCalendar->syncAgenda($agenda);

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
        $this->googleCalendar->syncAgenda($agendaRapat);

        return redirect()
            ->route('agenda-rapat.index')
            ->with('status', 'Agenda rapat berhasil diperbarui.');
    }

    /**
     * Hapus satu agenda rapat.
     */
    public function destroy(AgendaRapat $agendaRapat): RedirectResponse
    {
        $this->googleCalendar->removeAgenda($agendaRapat);
        $agendaRapat->delete();

        return redirect()
            ->route('agenda-rapat.index')
            ->with('status', 'Agenda rapat berhasil dihapus.');
    }
}

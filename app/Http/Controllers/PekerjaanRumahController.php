<?php

namespace App\Http\Controllers;

use App\Models\PekerjaanRumah;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PekerjaanRumahController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendar)
    {
    }

    /**
     * Aturan validasi untuk tambah/ubah pekerjaan rumah.
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
     * Halaman daftar PR (tabel + modal tambah/ubah/hapus).
     */
    public function index(): View
    {
        return view('v_pekerjaan_rumah', [
            'judul'       => 'Pekerjaan Rumah (PR)',
            'routePrefix' => 'pr',
            // Deadline terdekat dulu; baris tanpa deadline (opsional) tetap di akhir.
            'pekerjaans' => PekerjaanRumah::orderByRaw('deadline IS NULL')
                ->orderBy('deadline')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Tambah satu pekerjaan rumah.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $pr = PekerjaanRumah::create($data);
        $this->googleCalendar->syncPekerjaanRumah($pr);

        return redirect()
            ->route('pr.index')
            ->with('status', 'PR berhasil ditambahkan.');
    }

    /**
     * Perbarui satu pekerjaan rumah.
     */
    public function update(Request $request, PekerjaanRumah $pekerjaanRumah): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $pekerjaanRumah->update($data);
        $this->googleCalendar->syncPekerjaanRumah($pekerjaanRumah);

        return redirect()
            ->route('pr.index')
            ->with('status', 'PR berhasil diperbarui.');
    }

    /**
     * Hapus satu pekerjaan rumah.
     */
    public function destroy(PekerjaanRumah $pekerjaanRumah): RedirectResponse
    {
        $this->googleCalendar->removePekerjaanRumah($pekerjaanRumah);
        $pekerjaanRumah->delete();

        return redirect()
            ->route('pr.index')
            ->with('status', 'PR berhasil dihapus.');
    }
}

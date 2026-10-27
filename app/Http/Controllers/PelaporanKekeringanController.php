<?php

namespace App\Http\Controllers;

use App\Models\PelaporanKekeringan;
use App\Models\PelaporanKekeringanInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Monitoring Pelaporan Kekeringan (Super Admin): daftar petugas TRC statis dari
 * config/kekeringan.php; yang disimpan hanya status/jenis pelaporan dan waktu status.
 */
class PelaporanKekeringanController extends Controller
{
    private function authorize(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Semua kode petugas di config (urutan tampil).
     */
    private function semuaKode(): array
    {
        return collect(config('kekeringan.grup', []))->pluck('anggota')->flatten(1)->pluck('kode')->all();
    }

    public function index(): View
    {
        $this->authorize();

        return view('v_pelaporan_kekeringan', [
            'grup' => config('kekeringan.grup', []),
            'status' => PelaporanKekeringan::all()->keyBy('kode'),
            'info' => PelaporanKekeringanInfo::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize();

        $data = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'jam' => ['nullable', 'date_format:H:i'],
            'lapor' => ['array'],
            'lapor.*' => ['array'],
        ], [], ['tanggal' => 'tanggal status', 'jam' => 'jam status']);

        $lapor = $data['lapor'] ?? [];

        DB::transaction(function () use ($data, $lapor) {
            PelaporanKekeringanInfo::current()->update([
                'tanggal' => $data['tanggal'] ?? null,
                'jam' => $data['jam'] ?? null,
            ]);

            // Kotak yang tidak dicentang tidak terkirim: anggap false
            foreach ($this->semuaKode() as $kode) {
                $baris = $lapor[$kode] ?? [];
                $kejadian = ! empty($baris['kejadian_baru']);
                $penanganan = ! empty($baris['penanganan']);

                PelaporanKekeringan::updateOrCreate(['kode' => $kode], [
                    // Ada jenis pelaporan berarti sudah melapor
                    'sudah_lapor' => ! empty($baris['sudah_lapor']) || $kejadian || $penanganan,
                    'kejadian_baru' => $kejadian,
                    'penanganan' => $penanganan,
                ]);
            }
        });

        return redirect()->route('pelaporan-kekeringan.index')->with('status', 'Status pelaporan berhasil disimpan.');
    }

    /**
     * Refresh: semua petugas kembali "Belum Melaporkan" dan jenis pelaporan dikosongkan.
     */
    public function reset(): RedirectResponse
    {
        $this->authorize();

        PelaporanKekeringan::query()->update([
            'sudah_lapor' => false,
            'kejadian_baru' => false,
            'penanganan' => false,
        ]);

        return redirect()->route('pelaporan-kekeringan.index')->with('status', 'Semua status pelaporan dikembalikan ke "Belum Melaporkan".');
    }
}

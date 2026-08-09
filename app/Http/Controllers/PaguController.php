<?php

namespace App\Http\Controllers;

use App\Models\PaguRevision;
use App\Support\Satker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaguController extends Controller
{
    /**
     * Halaman kelola data revisi pagu (tabel editable).
     */
    public function index(): View
    {
        return view('v_pagu', [
            'revisions' => PaguRevision::orderBy('urutan')->orderBy('tanggal')->get(),
            'groups'    => Satker::groups(),
        ]);
    }

    /**
     * Simpan seluruh data revisi (ganti total: hapus lalu buat ulang).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'revisions'                => ['array'],
            'revisions.*.tanggal'      => ['nullable', 'date'],
            'revisions.*.keterangan'   => ['nullable', 'string', 'max:255'],
            'revisions.*.nilai'        => ['array'],
            'revisions.*.nilai.*'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $groups = array_keys(Satker::groups());

        DB::transaction(function () use ($data, $groups) {
            PaguRevision::query()->delete();

            $urutan = 0;
            foreach ($data['revisions'] ?? [] as $row) {
                // Lewati baris tanpa keterangan (baris kosong).
                if (blank($row['keterangan'] ?? null)) {
                    continue;
                }

                $nilai = [];
                foreach ($groups as $slug) {
                    $v = $row['nilai'][$slug] ?? null;
                    $nilai[$slug] = ($v === null || $v === '') ? 0 : (float) $v;
                }

                PaguRevision::create([
                    'urutan'     => $urutan++,
                    'tanggal'    => $row['tanggal'] ?: null,
                    'keterangan' => $row['keterangan'],
                    'nilai'      => $nilai,
                ]);
            }
        });

        return redirect()
            ->route('pagu.index')
            ->with('status', 'Data revisi pagu berhasil disimpan.');
    }
}

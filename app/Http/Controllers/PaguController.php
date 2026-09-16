<?php

namespace App\Http\Controllers;

use App\Models\PaguRevision;
use App\Support\Satker;
use Carbon\Carbon;
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

            // Collect & filter non-empty rows
            $rows = [];
            foreach ($data['revisions'] ?? [] as $row) {
                if (!blank($row['keterangan'] ?? null)) {
                    $rows[] = $row;
                }
            }

            // Sort by tanggal (chronological order, nulls last)
            usort($rows, function ($a, $b) {
                $dateA = $a['tanggal'] ? Carbon::createFromFormat('Y-m-d', $a['tanggal'])->timestamp : PHP_INT_MAX;
                $dateB = $b['tanggal'] ? Carbon::createFromFormat('Y-m-d', $b['tanggal'])->timestamp : PHP_INT_MAX;
                return $dateA <=> $dateB;
            });

            // Create sorted data with urutan
            $urutan = 0;
            foreach ($rows as $row) {
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

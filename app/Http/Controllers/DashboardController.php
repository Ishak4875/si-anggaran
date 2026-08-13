<?php

namespace App\Http\Controllers;

use App\Models\DitjenProgres;
use App\Models\Packet;
use App\Services\PacketSyncService;
use App\Support\Satker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Dashboard: rekap per satker (pagu, rata-rata progres keuangan & realisasi fisik).
     */
    public function index(): View
    {
        // Agregasi per grup satker langsung di DB.
        $agg = Packet::query()
            ->selectRaw('satker_group')
            ->selectRaw('COUNT(*) as jml_paket')
            ->selectRaw('SUM(pagu) as total_pagu')
            ->selectRaw('SUM(realisasi) as total_realisasi')
            // Realisasi fisik tertimbang pagu: SUM(real_fisik * pagu) / SUM(pagu).
            ->selectRaw('SUM(real_fisik * pagu) as fisik_num')
            ->selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN pagu ELSE 0 END) as fisik_den')
            ->groupBy('satker_group')
            ->get()
            ->keyBy('satker_group');

        // Susun baris sesuai urutan config agar 5 satker selalu tampil.
        $rows = [];
        $grandFisikNum = 0.0;
        $grandFisikDen = 0.0;
        foreach (Satker::groups() as $slug => $group) {
            $data = $agg->get($slug);

            $totalPagu = (float) ($data->total_pagu ?? 0);
            $totalReal = (float) ($data->total_realisasi ?? 0);
            $fisikNum  = (float) ($data->fisik_num ?? 0);
            $fisikDen  = (float) ($data->fisik_den ?? 0);

            $grandFisikNum += $fisikNum;
            $grandFisikDen += $fisikDen;

            $rows[] = [
                'slug'                 => $slug,
                'nama'                 => $group['nama'],
                'singkatan'            => $group['singkatan'],
                'jml_paket'            => (int) ($data->jml_paket ?? 0),
                'total_pagu'           => $totalPagu,
                'total_realisasi'      => $totalReal,
                // Progres keuangan = total realisasi / total pagu x 100 (per satker).
                'avg_progres_keuangan' => $totalPagu > 0 ? $totalReal / $totalPagu * 100 : null,
                // Realisasi fisik = SUM(real_fisik x pagu) / SUM(pagu) (per satker).
                'avg_real_fisik'       => $fisikDen > 0 ? $fisikNum / $fisikDen : null,
            ];
        }

        $grandPagu = array_sum(array_column($rows, 'total_pagu'));
        $grandReal = array_sum(array_column($rows, 'total_realisasi'));

        $totals = [
            'jml_paket'            => array_sum(array_column($rows, 'jml_paket')),
            'total_pagu'           => $grandPagu,
            'total_realisasi'      => $grandReal,
            // Progres keuangan keseluruhan = grand total realisasi / grand total pagu x 100.
            'avg_progres_keuangan' => $grandPagu > 0 ? $grandReal / $grandPagu * 100 : null,
            // Realisasi fisik keseluruhan = grand SUM(real_fisik x pagu) / grand SUM(pagu).
            'avg_real_fisik'       => $grandFisikDen > 0 ? $grandFisikNum / $grandFisikDen : null,
        ];

        $ditjen = DitjenProgres::current();

        return view('v_dashboard', [
            'rows'        => $rows,
            'totals'      => $totals,
            'lastSync'    => Packet::max('updated_at'),
            'paguChart'   => $this->buildPaguChart(),
            'ppkProgres'  => $this->buildPpkProgres(),
            'unmapped'    => $this->buildUnmappedPpk(),
            'ditjen'      => ['keu' => $ditjen->keu, 'fis' => $ditjen->fis],
            'statusJam'   => $this->buildStatusJam(),
        ]);
    }

    /**
     * Status "checkpoint" laporan (08:00 / 12:00 / 16:00 WIB) berdasarkan jam
     * saat ini, mengikuti pola pelaporan 4-jaman: checkpoint dianggap berlaku
     * mulai 1 jam setelah waktunya sampai checkpoint berikutnya.
     */
    private function buildStatusJam(): array
    {
        $now = now('Asia/Makassar'); // "jam di laptop" — konsisten dgn konversi WITA yg dipakai di aplikasi ini
        $menit = $now->hour * 60 + $now->minute;

        if ($menit >= 9 * 60 && $menit <= 13 * 60) {
            $checkpoint = '08:00';
            $tanggal = $now->copy();
        } elseif ($menit >= 13 * 60 + 1 && $menit <= 17 * 60) {
            $checkpoint = '12:00';
            $tanggal = $now->copy();
        } else {
            $checkpoint = '16:00';
            // Dini hari (00:00–08:59): checkpoint 16:00 sebenarnya terjadi kemarin.
            $tanggal = $menit < 9 * 60 ? $now->copy()->subDay() : $now->copy();
        }

        return [
            'tanggal' => $tanggal->locale('id')->translatedFormat('d F Y'),
            'jam'     => $checkpoint,
        ];
    }

    /**
     * Perbarui nilai progres Ditjen SDA (input manual, dipakai sebagai acuan
     * pembanding pada tabel "Progres per PPK & Peringkat").
     */
    public function updateDitjenProgres(Request $request): RedirectResponse
    {
        // Input kosong dari form dikirim sebagai string kosong, bukan null.
        $request->merge([
            'keu' => $request->input('keu') === '' ? null : $request->input('keu'),
            'fis' => $request->input('fis') === '' ? null : $request->input('fis'),
        ]);

        $data = $request->validate([
            'keu' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fis' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DitjenProgres::current()->update($data);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Progres Ditjen SDA berhasil diperbarui.');
    }

    /**
     * Deteksi paket yang BELUM ditetapkan PPK-nya (ppk_id NULL), dikelompokkan
     * per satker. Berguna untuk paket-paket baru hasil sinkronisasi.
     *
     * @return array{total:int, satkers:array}
     */
    private function buildUnmappedPpk(): array
    {
        $rows = Packet::whereNull('ppk_id')
            ->orderBy('satker_group')
            ->orderBy('kdpaket')
            ->get(['id', 'satker_group', 'kdpaket', 'nmpaket', 'pagu']);

        $bySlug = $rows->groupBy('satker_group');

        $satkers = [];
        foreach (Satker::groups() as $slug => $group) {
            $list = $bySlug->get($slug);
            if ($list === null || $list->isEmpty()) {
                continue; // hanya tampilkan satker yang masih ada paket belum dipetakan
            }

            $satkers[] = [
                'slug'      => $slug,
                'nama'      => $group['nama'],
                'singkatan' => $group['singkatan'],
                'jml'       => $list->count(),
                'packets'   => $list->values(),
            ];
        }

        return [
            'total'   => $rows->count(),
            'satkers' => $satkers,
        ];
    }

    /**
     * Susun progres per satker beserta rincian per PPK, lengkap dengan peringkat
     * (rank berdasarkan progres keuangan menurun).
     *
     * @return array{satkers:array, total:array}
     */
    private function buildPpkProgres(): array
    {
        // Agregasi per (satker, ppk) — PPK diambil dari tabel ppks via relasi ppk_id.
        $agg = Packet::query()
            ->leftJoin('ppks', 'packets.ppk_id', '=', 'ppks.id')
            ->selectRaw('packets.satker_group as satker_group')
            ->selectRaw('packets.ppk_id as ppk_id')
            ->selectRaw('MAX(ppks.jabatan) as ppk_nama')
            ->selectRaw('MAX(ppks.nama) as pejabat')
            ->selectRaw('MAX(ppks.urutan) as urut')
            ->selectRaw('COUNT(*) as jml')
            ->selectRaw('SUM(packets.pagu) as pagu')
            ->selectRaw('SUM(packets.realisasi) as realisasi')
            ->selectRaw('SUM(packets.real_fisik * packets.pagu) as fnum')
            ->selectRaw('SUM(CASE WHEN packets.real_fisik IS NOT NULL THEN packets.pagu ELSE 0 END) as fden')
            ->groupBy('packets.satker_group', 'packets.ppk_id')
            ->get();

        // Bentuk baris PPK per satker.
        $perSatker = [];
        foreach ($agg as $r) {
            $pagu = (float) $r->pagu;
            $real = (float) $r->realisasi;
            $fnum = (float) $r->fnum;
            $fden = (float) $r->fden;

            $perSatker[$r->satker_group][] = [
                'slug'      => $r->satker_group,
                'ppk_id'    => $r->ppk_id,
                'ppk'       => $r->ppk_nama ?: '(Belum ada PPK)',
                'pejabat'   => $r->pejabat,
                'urut'      => $r->urut ?? 999,
                'jml'       => (int) $r->jml,
                'pagu'      => $pagu,
                'realisasi' => $real,
                'keu'       => $pagu > 0 ? $real / $pagu * 100 : null,
                // real_fisik sudah dalam persen → rata-rata tertimbang, tanpa x100.
                'fis'       => $fden > 0 ? $fnum / $fden : null,
            ];
        }

        // Kumpulan seluruh PPK untuk peringkat global (by keu desc).
        $allPpk = [];
        foreach ($perSatker as $list) {
            foreach ($list as $p) {
                $allPpk[] = $p;
            }
        }
        $ppkRank = $this->rankByKeu($allPpk);

        // Susun struktur per satker sesuai urutan config.
        $satkers = [];
        $satkerAgg = [];
        foreach (Satker::groups() as $slug => $group) {
            $list = $perSatker[$slug] ?? [];

            // urutkan PPK sesuai urutan Excel.
            usort($list, fn ($a, $b) => $a['urut'] <=> $b['urut']);

            $pagu = array_sum(array_column($list, 'pagu'));
            $real = array_sum(array_column($list, 'realisasi'));

            // Tetapkan rank tiap PPK.
            foreach ($list as &$p) {
                $p['rank'] = $ppkRank[$this->ppkKey($p)] ?? null;
            }
            unset($p);

            $satkers[$slug] = [
                'slug'      => $slug,
                'nama'      => $group['nama'],
                'singkatan' => $group['singkatan'],
                'ppks'      => $list,
                'pagu'      => $pagu,
                'realisasi' => $real,
            ];
            $satkerAgg[$slug] = ['pagu' => $pagu, 'realisasi' => $real];
        }

        // Progres satker (keu = real/pagu; fis = weighted by pagu) — ambil langsung dari DB.
        $satkerFis = Packet::query()
            ->selectRaw('satker_group')
            ->selectRaw('SUM(real_fisik * pagu) as fnum')
            ->selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN pagu ELSE 0 END) as fden')
            ->groupBy('satker_group')
            ->get()
            ->keyBy('satker_group');

        foreach ($satkers as $slug => &$s) {
            $s['keu'] = $s['pagu'] > 0 ? $s['realisasi'] / $s['pagu'] * 100 : null;
            $f = $satkerFis->get($slug);
            $fden = $f ? (float) $f->fden : 0;
            $s['fis'] = $fden > 0 ? (float) $f->fnum / $fden : null;
        }
        unset($s);

        // Rank satker (by keu desc).
        $satkerRank = $this->rankByKeu(array_map(
            fn ($s) => ['keu' => $s['keu'], 'key' => $s['slug']],
            $satkers
        ), 'key');
        foreach ($satkers as $slug => &$s) {
            $s['rank'] = $satkerRank[$slug] ?? null;
        }
        unset($s);

        // Total keseluruhan.
        $totPagu = array_sum(array_column($satkerAgg, 'pagu'));
        $totReal = array_sum(array_column($satkerAgg, 'realisasi'));
        $totF = Packet::query()
            ->selectRaw('SUM(real_fisik * pagu) as fnum, SUM(CASE WHEN real_fisik IS NOT NULL THEN pagu ELSE 0 END) as fden')
            ->first();

        return [
            'satkers' => array_values($satkers),
            'total'   => [
                'pagu'      => $totPagu,
                'realisasi' => $totReal,
                'keu'       => $totPagu > 0 ? $totReal / $totPagu * 100 : null,
                'fis'       => ($totF && (float) $totF->fden > 0) ? (float) $totF->fnum / (float) $totF->fden : null,
            ],
        ];
    }

    /**
     * Beri peringkat berdasarkan 'keu' menurun. Mengembalikan [key => rank].
     * Item tanpa keu (null) diletakkan di akhir.
     */
    private function rankByKeu(array $items, ?string $keyField = null): array
    {
        $withKey = [];
        foreach ($items as $it) {
            $key = $keyField ? $it[$keyField] : $this->ppkKey($it);
            $withKey[] = ['key' => $key, 'keu' => $it['keu'] ?? null];
        }

        usort($withKey, function ($a, $b) {
            if ($a['keu'] === null && $b['keu'] === null) return 0;
            if ($a['keu'] === null) return 1;
            if ($b['keu'] === null) return -1;
            return $b['keu'] <=> $a['keu'];
        });

        $rank = [];
        foreach ($withKey as $i => $it) {
            $rank[$it['key']] = $i + 1;
        }

        return $rank;
    }

    /**
     * Kunci unik untuk sebuah baris PPK (satker + nama ppk).
     */
    private function ppkKey(array $ppk): string
    {
        return ($ppk['slug'] ?? '') . '|' . ($ppk['ppk_id'] ?? 'none');
    }

    /**
     * Susun data grafik revisi pagu (stacked column) dari tabel pagu_revisions.
     *
     * @return array{series:array, categories:array, colors:array, totals:array, empty:bool}
     */
    private function buildPaguChart(): array
    {
        $revisions = \App\Models\PaguRevision::orderBy('urutan')->orderBy('tanggal')->get();
        $groups    = Satker::groups();

        // Urutan penumpukan satker pada grafik (fallback ke urutan config).
        $order = collect(config('satker.chart_order', array_keys($groups)))
            ->filter(fn ($slug) => isset($groups[$slug]))
            ->values()
            ->all();
        foreach (array_keys($groups) as $slug) {
            if (! in_array($slug, $order, true)) {
                $order[] = $slug;
            }
        }

        $categories = [];
        $totals     = [];
        foreach ($revisions as $rev) {
            $total        = $rev->total();
            $totals[]     = $total;
            $categories[] = [
                number_format($total, 0, ',', '.'),
                optional($rev->tanggal)->translatedFormat('d M Y') ?? '',
                $rev->keterangan,
            ];
        }

        $series = [];
        $colors = [];
        foreach ($order as $slug) {
            $colors[] = $groups[$slug]['warna'] ?? '#6c757d';
            $data     = [];
            foreach ($revisions as $rev) {
                $data[] = (float) ($rev->nilai[$slug] ?? 0);
            }
            $series[] = [
                'name' => $groups[$slug]['singkatan'] ?? $slug,
                'data' => $data,
            ];
        }

        return [
            'series'     => $series,
            'categories' => $categories,
            'colors'     => $colors,
            'totals'     => $totals,
            'empty'      => $revisions->isEmpty(),
        ];
    }

    /**
     * Daftar seluruh paket kegiatan untuk satu satker.
     */
    public function satker(string $slug): View
    {
        $group = Satker::find($slug);

        abort_if($group === null, 404, 'Satker tidak ditemukan.');

        $packets = Packet::with('ppk')
            ->where('satker_group', $slug)
            ->orderBy('kdpaket')
            ->get();

        $totalPagu = (float) $packets->sum('pagu');
        $totalReal = (float) $packets->sum('realisasi');

        // Realisasi fisik tertimbang pagu: SUM(real_fisik x pagu) / SUM(pagu).
        $fisikNum = $packets->sum(fn ($p) => $p->real_fisik !== null ? $p->real_fisik * (float) $p->pagu : 0);
        $fisikDen = (float) $packets->whereNotNull('real_fisik')->sum('pagu');

        // Pilihan PPK khusus satker ini (untuk dropdown penetapan PPK per paket).
        $ppkOptions = \App\Models\Ppk::where('satker_group', $slug)
            ->orderBy('urutan')
            ->get(['id', 'jabatan', 'nama']);

        return view('v_satker', [
            'slug'       => $slug,
            'group'      => $group,
            'packets'    => $packets,
            'ppkOptions' => $ppkOptions,
            'ringkas'  => [
                'jml_paket'   => $packets->count(),
                'total_pagu'  => $totalPagu,
                'total_real'  => $totalReal,
                // Progres keuangan = total realisasi / total pagu x 100.
                'avg_keu'     => $totalPagu > 0 ? $totalReal / $totalPagu * 100 : null,
                // Realisasi fisik = SUM(real_fisik x pagu) / SUM(pagu).
                'avg_fisik'   => $fisikDen > 0 ? $fisikNum / $fisikDen : null,
            ],
        ]);
    }

    /**
     * Simpan penetapan PPK untuk paket-paket pada satu satker.
     * Pilihan PPK dibatasi hanya PPK milik satker tersebut.
     */
    public function assignPpk(Request $request, string $slug): RedirectResponse
    {
        $group = Satker::find($slug);
        abort_if($group === null, 404, 'Satker tidak ditemukan.');

        // ID PPK yang sah untuk satker ini.
        $validPpkIds = \App\Models\Ppk::where('satker_group', $slug)->pluck('id')->all();

        $data = $request->validate([
            'ppk'   => ['array'],
            'ppk.*' => ['nullable', 'integer'],
        ]);

        foreach ($data['ppk'] ?? [] as $packetId => $ppkId) {
            $ppkId = ($ppkId === '' || $ppkId === null) ? null : (int) $ppkId;

            // Tolak PPK di luar satker ini.
            if ($ppkId !== null && ! in_array($ppkId, $validPpkIds, true)) {
                continue;
            }

            Packet::where('id', (int) $packetId)
                ->where('satker_group', $slug) // pastikan paket memang milik satker ini
                ->update(['ppk_id' => $ppkId]);
        }

        return redirect()
            ->route('satker.show', $slug)
            ->with('status', 'Penetapan PPK berhasil disimpan.');
    }

    /**
     * Tarik ulang data dari API lalu simpan ke DB.
     */
    public function sync(PacketSyncService $service): RedirectResponse
    {
        try {
            $result = $service->sync();
        } catch (Throwable $e) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Gagal sinkronisasi data: ' . $e->getMessage());
        }

        return redirect()
            ->route('dashboard')
            ->with('status', "Sinkronisasi berhasil. {$result['total']} paket tersimpan.");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Packet;
use App\Models\Ppk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestorePpk extends Command
{
    protected $signature = 'ppk:restore
                            {--dry-run : Tampilkan apa yang akan diubah tanpa menyimpan}
                            {--force : Timpa juga paket yang PPK-nya sudah terisi}';

    protected $description = 'Kembalikan penetapan PPK paket dari database/data/ppk_assignments.json (dicocokkan lewat kdpaket dan jabatan PPK)';

    public function handle(): int
    {
        $path = database_path('data/ppk_assignments.json');
        $data = json_decode((string) @file_get_contents($path), true);

        if (! is_array($data) || ! isset($data['penetapan']) || ! is_array($data['penetapan'])) {
            $this->error("File {$path} tidak ditemukan atau formatnya tidak dikenali.");

            return self::FAILURE;
        }

        $map = $data['penetapan']; // kdpaket => jabatan PPK

        // Cocokkan lewat jabatan, bukan id: id PPK di server bisa berbeda
        // (mis. PPK pernah dihapus lalu dibuat ulang).
        $ppks = Ppk::all()->keyBy(fn (Ppk $p) => mb_strtolower(trim($p->jabatan)));

        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $diisi = $sudahTerisi = $tidakAdaDiFile = $satkerBeda = 0;
        $jabatanHilang = [];
        $updates = []; // packet id => ppk id

        foreach (Packet::query()->get(['id', 'kdpaket', 'satker_group', 'ppk_id']) as $packet) {
            $jabatan = $map[$packet->kdpaket] ?? null;

            if ($jabatan === null) {
                $tidakAdaDiFile++;
                continue;
            }

            $ppk = $ppks->get(mb_strtolower(trim($jabatan)));

            if ($ppk === null) {
                $jabatanHilang[$jabatan] = ($jabatanHilang[$jabatan] ?? 0) + 1;
                continue;
            }

            // Pengaman: PPK harus milik satker yang sama dengan paketnya.
            if ($ppk->satker_group !== $packet->satker_group) {
                $satkerBeda++;
                continue;
            }

            if ($packet->ppk_id !== null && ! $force) {
                $sudahTerisi++;
                continue;
            }

            if ((int) $packet->ppk_id === (int) $ppk->id) {
                $sudahTerisi++;
                continue;
            }

            $updates[$packet->id] = $ppk->id;
            $diisi++;
        }

        if (! $dryRun && $updates) {
            DB::transaction(function () use ($updates) {
                foreach ($updates as $packetId => $ppkId) {
                    Packet::whereKey($packetId)->update(['ppk_id' => $ppkId]);
                }
            });
        }

        $this->info(($dryRun ? '[DRY RUN] Akan diisi' : 'Diisi') . ": {$diisi} paket");
        $this->line("Sudah terisi (dilewati): {$sudahTerisi}" . ($force ? '' : ' — pakai --force untuk menimpa'));
        $this->line("Tidak ada di file (paket baru dari API): {$tidakAdaDiFile}");

        if ($satkerBeda) {
            $this->warn("Dilewati karena satker PPK berbeda dengan satker paket: {$satkerBeda}");
        }

        if ($jabatanHilang) {
            $this->warn('Jabatan PPK di file yang tidak ada di tabel PPK server (tambahkan lewat Kelola PPK lalu jalankan ulang):');
            foreach ($jabatanHilang as $jabatan => $jml) {
                $this->line("  - {$jabatan}: {$jml} paket");
            }
        }

        $this->line('Paket tanpa PPK sekarang: ' . Packet::whereNull('ppk_id')->count() . ' dari ' . Packet::count());

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\PacketSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncPackets extends Command
{
    protected $signature = 'packets:sync';

    protected $description = 'Ambil seluruh data paket dari API SIHKA dan simpan ke database';

    public function handle(PacketSyncService $service): int
    {
        $this->info('Mengambil data dari API SIHKA...');

        try {
            $result = $service->sync();
        } catch (Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Berhasil menyimpan {$result['total']} paket.");

        foreach ($result['per_satker'] as $slug => $jml) {
            $this->line("  - {$slug}: {$jml} paket");
        }

        return self::SUCCESS;
    }
}

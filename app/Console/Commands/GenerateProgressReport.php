<?php

namespace App\Console\Commands;

use App\Models\DitjenProgres;
use App\Services\DashboardScreenshotService;
use App\Services\ProgressReportService;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateProgressReport extends Command
{
    protected $signature = 'progress:report';

    protected $description = 'Generate WhatsApp progress report dengan screenshot dashboard';

    public function handle()
    {
        $this->info('=================================================');
        $this->info('  Progress e-Monitoring Report Generator');
        $this->info('=================================================');
        $this->line('');

        // Step 1: Input Ditjen SDA values
        if (!$this->inputDitjenProgress()) {
            $this->error('Proses dibatalkan.');
            return self::FAILURE;
        }

        $this->line('');

        // Step 2: API Sync
        if (!$this->syncApi()) {
            return self::FAILURE;
        }

        $this->line('');

        // Step 3: Build Report
        if (!$this->buildReport()) {
            return self::FAILURE;
        }

        $this->line('');
        $this->info('✓ Report siap untuk dikirim ke WA!');

        return self::SUCCESS;
    }

    /**
     * Step 1: Ask and confirm Ditjen SDA values
     */
    private function inputDitjenProgress(): bool
    {
        $this->line('Step 1: Input Ditjen SDA Progress');
        $this->line('─────────────────────────────────');

        $keu = $this->ask('Progres Keuangan Ditjen SDA (%) [0-100]', null);
        $fis = $this->ask('Realisasi Fisik Ditjen SDA (%) [0-100]', null);

        // Validate input
        if (!is_numeric($keu) || !is_numeric($fis)) {
            $this->error('Input harus berupa angka!');
            return false;
        }

        $keu = (float) $keu;
        $fis = (float) $fis;

        if ($keu < 0 || $keu > 100 || $fis < 0 || $fis > 100) {
            $this->error('Nilai harus antara 0-100!');
            return false;
        }

        $this->line('');
        $this->table(['Metrik', 'Nilai'], [
            ['Progres Keuangan', sprintf('%.2f %%', $keu)],
            ['Realisasi Fisik', sprintf('%.2f %%', $fis)],
        ]);

        $this->line('');
        if (!$this->confirm('Lanjutkan dengan nilai di atas?')) {
            return false;
        }

        // Update database
        DitjenProgres::current()->update([
            'keu' => $keu,
            'fis' => $fis,
        ]);

        $this->info('✓ Nilai Ditjen SDA disimpan');

        return true;
    }

    /**
     * Step 2: Run API sync
     */
    private function syncApi(): bool
    {
        $this->line('Step 2: Sinkronisasi Data API');
        $this->line('─────────────────────────────');

        try {
            $this->line('Menjalankan packets:sync...');

            Artisan::call('packets:sync');

            $this->info('✓ API sync selesai');

            return true;
        } catch (\Exception $e) {
            $this->error('Error saat sync API: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Step 3: Build and display report
     */
    private function buildReport(): bool
    {
        $this->line('Step 3: Build Progress Report');
        $this->line('────────────────────────────');

        try {
            // Generate report data
            $this->line('Mengagregasi data paket...');
            $reportService = app(ProgressReportService::class);
            $data = $reportService->build();
            $this->info('✓ Data aggregated');

            // Format message
            $this->line('Memformat pesan WA...');
            $formatter = app(WhatsAppMessageFormatter::class);
            $message = $formatter->format($data);
            $this->info('✓ Pesan diformat');

            // Capture screenshot (or get instruction)
            $this->line('Persiapan screenshot...');
            $screenshotService = app(DashboardScreenshotService::class);
            $screenshotInfo = $screenshotService->capture();

            if (is_array($screenshotInfo)) {
                $this->info("📸 Instruction: {$screenshotInfo['instruction']}");
                $screenshotDisplay = "Manual: Buka {$screenshotInfo['url']}, klik 'Unduh Gambar' pada chart";
            } else {
                $this->info("✓ Screenshot disimpan: {$screenshotInfo}");
                $screenshotDisplay = $screenshotInfo;
            }

            // Display results
            $this->line('');
            $this->line('═══════════════════════════════════════════════════════');
            $this->info('📋 PESAN WA (siap copy-paste)');
            $this->line('═══════════════════════════════════════════════════════');
            $this->line('');
            $this->line($message);
            $this->line('');
            $this->line('═══════════════════════════════════════════════════════');
            $this->info("📸 SCREENSHOT: {$screenshotDisplay}");
            $this->line('═══════════════════════════════════════════════════════');
            $this->line('');

            return true;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return false;
        }
    }
}

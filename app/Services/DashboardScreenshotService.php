<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;

class DashboardScreenshotService
{
    /**
     * Capture screenshot of dashboard chart
     *
     * Returns instruction for user to manually capture from dashboard,
     * since browser automation has dependency conflicts with current PHP version.
     *
     * @return array Screenshot instruction with dashboard URL
     */
    public function capture(): array
    {
        $storagePath = config('progress.storage_path', 'progress');

        // Ensure storage directory exists
        if (!Storage::exists($storagePath)) {
            Storage::makeDirectory($storagePath);
        }

        $dashboardUrl = config('app.url') . '/';

        return [
            'method' => 'manual',
            'instruction' => 'Buka dashboard dan klik tombol "Unduh Gambar" pada chart "Progres per PPK & Peringkat"',
            'url' => $dashboardUrl,
            'button' => 'Unduh Gambar (button pada chart section)',
            'note' => 'File akan otomatis di-download ke folder Downloads Anda',
        ];
    }

    /**
     * Alternative: Use headless Chrome/Puppeteer if installed locally
     * This would require running: npm install puppeteer globally
     *
     * @return string Path to downloaded file or instruction
     * @throws Exception
     */
    public function captureWithPuppeteer(): string
    {
        $storagePath = config('progress.storage_path', 'progress');

        try {
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            $filename = 'chart-' . now()->format('Y-m-d_H-i-s') . '.png';
            $fullPath = storage_path("app/{$storagePath}/{$filename}");
            $dashboardUrl = config('app.url') . '/';

            // Check if puppeteer is installed globally
            $which = shell_exec('which puppeteer');
            if (empty($which)) {
                throw new Exception('Puppeteer not installed. Run: npm install -g puppeteer');
            }

            // Create Node.js script to take screenshot
            $scriptPath = sys_get_temp_dir() . '/screenshot.js';
            $script = <<<JS
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto('$dashboardUrl', { waitUntil: 'networkidle2' });
    await page.screenshot({ path: '$fullPath', fullPage: true });
    await browser.close();
})();
JS;

            file_put_contents($scriptPath, $script);

            // Execute Node.js script
            exec("node {$scriptPath}", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('Puppeteer script failed: ' . implode("\n", $output));
            }

            if (!file_exists($fullPath)) {
                throw new Exception('Screenshot file was not created');
            }

            unlink($scriptPath);

            return "{$storagePath}/{$filename}";
        } catch (Exception $e) {
            throw new Exception("Failed to capture screenshot: " . $e->getMessage());
        }
    }

    /**
     * List all captured screenshots
     */
    public function listCaptures(): array
    {
        $storagePath = config('progress.storage_path', 'progress');

        if (!Storage::exists($storagePath)) {
            return [];
        }

        $files = Storage::files($storagePath);

        return array_map(function ($file) {
            return [
                'path' => $file,
                'url' => Storage::url($file),
                'size' => Storage::size($file),
            ];
        }, $files);
    }

    /**
     * Clean old screenshots (keep only last N files)
     */
    public function cleanup(int $keepLatest = 5): int
    {
        $storagePath = config('progress.storage_path', 'progress');

        if (!Storage::exists($storagePath)) {
            return 0;
        }

        $files = Storage::files($storagePath);

        if (count($files) <= $keepLatest) {
            return 0;
        }

        // Sort by modified time, newest first
        usort($files, function ($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });

        // Delete old files
        $deleted = 0;
        for ($i = $keepLatest; $i < count($files); $i++) {
            if (Storage::delete($files[$i])) {
                $deleted++;
            }
        }

        return $deleted;
    }
}

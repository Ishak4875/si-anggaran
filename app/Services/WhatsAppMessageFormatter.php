<?php

namespace App\Services;

use Carbon\Carbon;

class WhatsAppMessageFormatter
{
    /**
     * Format progress data into WhatsApp message
     */
    public function format(array $data): string
    {
        $greeting = $this->getGreeting();
        $bwsName = config('progress.bws_name', 'BWS Sul IV KDI');
        $ccName = config('progress.cc_name', 'Bapak Kabalai');

        $ditjen = $data['ditjen'];
        $bws = $data['bws'];
        $satkers = $data['satkers'];
        $ppks = $data['ppks'];
        $bottomPpk = $data['bottom_ppk'];
        $timestamp = $data['timestamp'];

        $deviasi_keu = round($bws['keu'] - $ditjen['keu'], 2);
        $deviasi_fis = round($bws['fis'] - $ditjen['fis'], 2);

        $message = "{$greeting} Bpk/Ibu\n";
        $message .= "\n";
        $message .= "Mohon izin menyampaikan progres e-Monitoring status :  {$timestamp}\n";
        $message .= "\n";

        // Ditjen SDA Section
        $message .= "🔘Progres K/F Ditjen SDA :\n";
        $message .= sprintf("Keuangan  : %.2f %%\n", $ditjen['keu']);
        $message .= sprintf("Fisik  : %.2f %%\n", $ditjen['fis']);
        $message .= "\n";

        // BWS Section
        $message .= "🔘Progres K/F {$bwsName} :\n";
        $message .= sprintf("Keuangan  : %.2f %%\n", $bws['keu']);
        $message .= sprintf("Fisik  : %.2f %%\n", $bws['fis']);
        $message .= "\n";

        // Deviasi Section
        $message .= "➡ Deviasi Progres K/F {$bwsName} thdp Ditjen SDA :\n";
        $message .= sprintf("Keuangan  : %.2f %%\n", $deviasi_keu);
        $message .= sprintf("Fisik  : %.2f %%\n", $deviasi_fis);
        $message .= "\n";

        // Satker Ranking Section
        $message .= "Rincian Progres K/F Masing2 Satker di lingkungan {$bwsName} :\n";
        foreach ($satkers as $idx => $s) {
            $emoji = $this->getRankEmoji($s['rank']);
            $name = $s['singkatan'];
            $keu = $s['keu'];
            $fis = $s['fis'];
            $message .= sprintf("🔹%s : %.2f %% / %.2f %%%s\n", $name, $keu, $fis, $emoji);
        }
        $message .= "============================\n";
        $message .= "\n";

        // PPK Ranking Section (all 17 PPK)
        $message .= "🔘 Progres K/F masing2 PPK di lingkungan {$bwsName}:\n";
        foreach ($ppks as $idx => $p) {
            $rank = $p['rank'];
            $emoji = $this->getRankEmoji($rank);
            $name = $this->shortPpkName($p['nama']);
            $keu = $p['keu'] !== null ? sprintf('%.2f', $p['keu']) : '—';
            $fis = $p['fis'] !== null ? sprintf('%.2f', $p['fis']) : '—';
            $message .= sprintf("%d. %s : %s %% / %s %%%s\n", $rank, $name, $keu, $fis, $emoji);
        }
        $message .= "\n";

        // Bottom Performer Section
        if ($bottomPpk) {
            $message .= "🔘  Progres Keu di bawah Ditjen SDA *berdasarkan iemon* :\n";
            $name = $this->shortPpkName($bottomPpk['nama']);
            $keu = sprintf('%.2f', $bottomPpk['keu']);
            $fis = sprintf('%.2f', $bottomPpk['fis']);
            $message .= sprintf("%d. %s : %s %% /  %s %%\n", $bottomPpk['rank'], $name, $keu, $fis);
            $message .= "\n";
        }

        // Footer
        $message .= "Demikian disampaikan, terima kasih..\n";
        $message .= "Salam Damai Indonesia, Bahagia untuk Semua 💪💪💪\n";
        $message .= "Cc. {$ccName}";

        return $message;
    }

    /**
     * Get dynamic greeting based on current time (Asia/Makassar)
     */
    private function getGreeting(): string
    {
        $hour = now()->setTimezone('Asia/Makassar')->hour;

        if ($hour < 11) {
            return 'Selamat Pagi';
        } elseif ($hour < 15) {
            return 'Selamat Siang';
        } elseif ($hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }

    /**
     * Get emoji for rank (🥇🥈🥉 for top 3)
     */
    private function getRankEmoji(int $rank): string
    {
        return match ($rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '',
        };
    }

    /**
     * Shorten PPK name for display
     * E.g., "PPK Air Tanah dan Air Baku 1" -> "PPK Atab I"
     */
    private function shortPpkName(string $name): string
    {
        // Map of common PPK names to shortened versions
        $map = [
            'PPK Air Tanah dan Air Baku 1' => 'PPK Atab I',
            'PPK Air Tanah dan Air Baku 2' => 'PPK Atab II',
            'PPK Air Tanah dan Air Baku 3' => 'PPK Atab III',
            'PPK Bendungan 1' => 'PPK Bend I',
            'PPK Bendungan 2' => 'PPK Bend II',
            'PPK Irigasi dan Rawa 1' => 'PPK Irwa I',
            'PPK Irigasi dan Rawa 2' => 'PPK Irwa II',
            'PPK Irigasi dan Rawa 3' => 'PPK Irwa III',
            'PPK Operasi dan Pemeliharaan SDA 1' => 'PPK OP I',
            'PPK Operasi dan Pemeliharaan SDA 2' => 'PPK OP II',
            'PPK Operasi dan Pemeliharaan SDA 3' => 'PPK OP III',
            'PPK Perencanaan dan Program' => 'PPK Perc',
            'PPK Perencanaan Bendungan' => 'PPK PrcBen',
            'PPK PSDA' => 'PPK PSDA',
            'PPK Sungai dan Pantai 1' => 'PPK Supan I',
            'PPK Sungai dan Pantai 2' => 'PPK Supan II',
            'PPK Tatalaksana' => 'PPK Ttl',
        ];

        foreach ($map as $full => $short) {
            if (strpos($name, $full) !== false) {
                return $short;
            }
        }

        // Fallback: return first 20 chars
        return substr($name, 0, 20);
    }
}

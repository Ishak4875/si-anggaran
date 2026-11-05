<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class StatusLaporan
{
    /**
     * Status "checkpoint" laporan (08:00 / 12:00 / 16:00 WIB) berdasarkan waktu
     * sync terakhir (WITA). Data iemon disimpan pada jam checkpoint WIB (= +1 jam
     * WITA) lalu disinkron 15 menit kemudian, jadi:
     *   sync >= 17:15 WITA -> 16:00 WIB
     *   sync >= 13:15 WITA -> 12:00 WIB
     *   sync >= 09:15 WITA -> 08:00 WIB
     *   sync <  09:15 WITA -> 16:00 WIB hari sebelumnya
     *
     * @return array{tanggal:string, jam:string}
     */
    public static function dariSync(?string $lastSync): array
    {
        // Tanpa data sync, pakai jam sekarang agar status tetap tampil.
        $sync = $lastSync
            ? Carbon::parse($lastSync, 'UTC')->setTimezone('Asia/Makassar')
            : now('Asia/Makassar');
        $menit = $sync->hour * 60 + $sync->minute;

        if ($menit >= 17 * 60 + 15) {
            $checkpoint = '16:00';
            $tanggal = $sync->copy();
        } elseif ($menit >= 13 * 60 + 15) {
            $checkpoint = '12:00';
            $tanggal = $sync->copy();
        } elseif ($menit >= 9 * 60 + 15) {
            $checkpoint = '08:00';
            $tanggal = $sync->copy();
        } else {
            $checkpoint = '16:00';
            $tanggal = $sync->copy()->subDay();
        }

        return [
            'tanggal' => $tanggal->locale('id')->translatedFormat('d F Y'),
            'jam'     => $checkpoint,
        ];
    }
}

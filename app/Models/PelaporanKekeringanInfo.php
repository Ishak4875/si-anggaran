<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelaporanKekeringanInfo extends Model
{
    protected $table = 'pelaporan_kekeringan_info';

    protected $fillable = ['tanggal', 'jam'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Baris tunggal (singleton) berisi tanggal & jam status pelaporan (WITA).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}

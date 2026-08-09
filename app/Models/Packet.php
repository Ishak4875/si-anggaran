<?php

namespace App\Models;

use App\Support\Satker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Packet extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pagu'             => 'decimal:2',
        'pagu51'           => 'decimal:2',
        'pagu52'           => 'decimal:2',
        'pagu53'           => 'decimal:2',
        'realisasi'        => 'decimal:2',
        'real_51'          => 'decimal:2',
        'real_52'          => 'decimal:2',
        'real_53'          => 'decimal:2',
        'progres_keuangan' => 'float',
        'real_fisik'       => 'float',
        'raw'              => 'array',
    ];

    /**
     * Nama lengkap satker dari slug grup.
     */
    public function getSatkerNamaAttribute(): string
    {
        return Satker::nama((string) $this->satker_group);
    }

    /**
     * PPK yang menangani paket ini.
     */
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(Ppk::class);
    }
}

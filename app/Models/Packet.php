<?php

namespace App\Models;

use App\Support\Satker;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Paket Inpres: kdpaket berawalan salah satu awalan di config/inpres.php.
     */
    public function scopeInpres(Builder $query): void
    {
        $prefixes = self::inpresPrefixes();

        if ($prefixes === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $q) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                $q->orWhere('packets.kdpaket', 'like', $prefix . '%');
            }
        });
    }

    /**
     * Paket Reguler untuk Rekap Progres: seluruh paket selain paket Inpres.
     * Bukan tabel master packets_reguler (Daftar Paket Reguler).
     */
    public function scopeReguler(Builder $query): void
    {
        $prefixes = self::inpresPrefixes();

        if ($prefixes === []) {
            return;
        }

        // kdpaket NULL tidak lolos NOT LIKE, padahal paket itu bukan Inpres.
        $query->where(function (Builder $q) use ($prefixes) {
            $q->whereNull('packets.kdpaket')->orWhere(function (Builder $bukanInpres) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $bukanInpres->where('packets.kdpaket', 'not like', $prefix . '%');
                }
            });
        });
    }

    /** @return list<string> */
    private static function inpresPrefixes(): array
    {
        return collect(config('inpres.prefixes', []))->flatten()->all();
    }
}

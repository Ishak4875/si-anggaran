<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ppk extends Model
{
    protected $guarded = [];

    /**
     * Daftar satker (pasti 5) untuk pilihan dropdown PPK.
     */
    public const SATKER_OPTIONS = [
        'Satker BWS Sulawesi IV',
        'SNVT PJPA Sulawesi IV Prov. Sulawesi Tenggara',
        'SNVT PJSA Sulawesi IV Prov. Sulawesi Tenggara',
        'SNVT Pembangunan Bendungan Sulawesi IV',
        'Satker OP SDA Sulawesi IV',
    ];

    /**
     * Peta nama satker (tampilan) → slug grup satker (config/satker.php).
     */
    public const SATKER_SLUG = [
        'Satker BWS Sulawesi IV'                        => 'balai',
        'SNVT PJPA Sulawesi IV Prov. Sulawesi Tenggara' => 'pjpa',
        'SNVT PJSA Sulawesi IV Prov. Sulawesi Tenggara' => 'pjsa',
        'SNVT Pembangunan Bendungan Sulawesi IV'        => 'bendungan',
        'Satker OP SDA Sulawesi IV'                     => 'op',
    ];

    protected static function booted(): void
    {
        // Selalu jaga satker_group (slug) sinkron dengan pilihan satker.
        static::saving(function (Ppk $ppk) {
            $ppk->satker_group = self::SATKER_SLUG[$ppk->satker] ?? null;
        });
    }

    /**
     * Paket-paket yang ditangani PPK ini.
     */
    public function packets(): HasMany
    {
        return $this->hasMany(Packet::class);
    }
}

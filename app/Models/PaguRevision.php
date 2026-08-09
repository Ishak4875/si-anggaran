<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaguRevision extends Model
{
    protected $guarded = [];

    protected $casts = [
        'nilai'   => 'array',
        'tanggal' => 'date',
    ];

    /**
     * Total seluruh satker pada revisi ini.
     */
    public function total(): float
    {
        return array_sum(array_map('floatval', $this->nilai ?? []));
    }
}

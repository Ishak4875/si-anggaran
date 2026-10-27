<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cctv extends Model
{
    protected $fillable = [
        'nama',
        'device_id',
        'kdpaket',
        'lokasi',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Paket tempat kamera terpasang (lewat kdpaket; null bila paket hilang dari data API).
     */
    public function packet(): BelongsTo
    {
        return $this->belongsTo(Packet::class, 'kdpaket', 'kdpaket');
    }

    /**
     * Kamera yang boleh dilihat user: akun PPK hanya kamera di paket PPK-nya.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === 'ppk') {
            $query->whereHas('packet', fn ($q) => $q->where('ppk_id', $user->ppk_id ?? 0));
        }

        return $query;
    }
}

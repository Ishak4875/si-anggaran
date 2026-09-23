<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PekerjaanRumah extends Model
{
    public const STATUS_OPTIONS = ['belum', 'proses', 'selesai'];

    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
    ];
}

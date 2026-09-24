<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PekerjaanRumahKepalaBalai extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaKepalaBalai extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_agenda' => 'date',
    ];
}

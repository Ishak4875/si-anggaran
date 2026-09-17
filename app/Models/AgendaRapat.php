<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaRapat extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_agenda' => 'date',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Status pelaporan kekeringan satu petugas (nama/jabatan di config/kekeringan.php).
 */
class PelaporanKekeringan extends Model
{
    protected $table = 'pelaporan_kekeringan';

    protected $fillable = ['kode', 'sudah_lapor', 'kejadian_baru', 'penanganan'];

    protected $casts = [
        'sudah_lapor' => 'boolean',
        'kejadian_baru' => 'boolean',
        'penanganan' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DitjenProgres extends Model
{
    protected $table = 'ditjen_progres';

    protected $fillable = ['keu', 'fis'];

    /**
     * Baris tunggal (singleton) berisi nilai progres Ditjen SDA yang diinput manual.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}

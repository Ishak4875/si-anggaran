<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacketsRegular extends Model
{
    protected $table = 'packets_reguler';
    protected $fillable = ['kode_paket', 'nama_paket', 'satker', 'pagu'];
    protected $casts = ['pagu' => 'integer'];

    public function packets()
    {
        return Packet::where('satker_group', $this->satker)
            ->where(function ($query) {
                $query->where('nmpaket', 'LIKE', '%' . $this->nama_paket . '%')
                    ->orWhere('nmpaket', 'LIKE', '%' . mb_substr($this->nama_paket, 0, 20) . '%');
            });
    }

    public function getRelatedPackets()
    {
        return $this->packets()->get();
    }

    public function getRealisasi()
    {
        return $this->packets()->sum('realisasi');
    }

    public function getPagu()
    {
        $packetsPagu = $this->packets()->sum('pagu');
        return $packetsPagu > 0 ? $packetsPagu : $this->pagu;
    }

    public function getProgresKeu()
    {
        $totalPagu = $this->getPagu();
        if ($totalPagu == 0) return 0;
        return round(($this->getRealisasi() / $totalPagu) * 100, 2);
    }

    public function getProgresFisik()
    {
        $packets = $this->getRelatedPackets();
        if ($packets->isEmpty()) return 0;

        $totalPagu = $packets->sum('pagu');
        if ($totalPagu == 0) return 0;

        $weightedFisik = $packets->sum(function ($packet) {
            return ($packet->real_fisik ?? 0) * $packet->pagu;
        });

        return round($weightedFisik / $totalPagu, 2);
    }
}

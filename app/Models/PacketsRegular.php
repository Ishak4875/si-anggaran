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
        $query = Packet::where('satker_group', $this->satker);

        // Priority 1: Match by exact kode_paket
        if (strpos($this->kode_paket, '.') !== false) {
            // kode_paket from API (has dots)
            return $query->where('kdpaket', $this->kode_paket);
        }

        // Priority 2: Fuzzy match by package name (for generated codes like OPA-001)
        return $query->where(function ($q) {
            $q->where('nmpaket', 'LIKE', '%' . $this->nama_paket . '%')
              ->orWhere('nmpaket', 'LIKE', '%' . mb_substr($this->nama_paket, 0, 25) . '%');
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

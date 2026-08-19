<?php

namespace App\Http\Controllers;

use App\Models\PacketsRegular;
use App\Models\Ppk;
use App\Support\Satker;

class PacketsRegularController extends Controller
{
    public function index()
    {
        $satkerOptions = Satker::getNamesMap();
        $packets = PacketsRegular::orderBy('satker')
            ->orderBy('nama_paket')
            ->get();

        $grouped = $packets->groupBy('satker');

        $data = [];
        foreach (Satker::SLUGS as $slug) {
            if (isset($grouped[$slug])) {
                $data[$slug] = [
                    'name' => $satkerOptions[$slug],
                    'packets' => $grouped[$slug],
                ];
            }
        }

        return view('v_packets_regular_index', [
            'data' => $data,
            'satkerOptions' => $satkerOptions,
        ]);
    }
}

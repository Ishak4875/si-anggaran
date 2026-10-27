<?php

namespace App\Http\Controllers;

use App\Services\TuyaCameraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CctvController extends Controller
{
    public function __construct(private TuyaCameraService $tuya)
    {
    }

    public function index()
    {
        return view('v_cctv', [
            'configured' => $this->tuya->isConfigured(),
        ]);
    }

    /**
     * URL siaran baru untuk pemutar (dipanggil saat tombol Putar ditekan / URL kedaluwarsa).
     */
    public function stream(Request $request): JsonResponse
    {
        $type = $request->query('type') === 'flv' ? 'flv' : 'hls';

        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            return response()->json(['url' => $this->tuya->allocateStreamUrl($type), 'type' => $type]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal memuat siaran: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Status kamera: online dan baterai (dipanggil berkala oleh halaman).
     */
    public function status(): JsonResponse
    {
        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            return response()->json($this->tuya->deviceStatus());
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal membaca status kamera: ' . $e->getMessage()], 502);
        }
    }
}

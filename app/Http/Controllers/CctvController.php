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
     * Gerakkan kamera: arah 0–7 (lihat TuyaCameraService::ptzMove) atau "stop".
     */
    public function ptz(Request $request): JsonResponse
    {
        $data = $request->validate([
            'arah' => ['required', 'in:0,1,2,3,4,5,6,7,stop'],
        ]);

        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            $data['arah'] === 'stop'
                ? $this->tuya->ptzStop()
                : $this->tuya->ptzMove((int) $data['arah']);

            return response()->json(['ok' => true]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal menggerakkan kamera: ' . $e->getMessage()], 502);
        }
    }
}

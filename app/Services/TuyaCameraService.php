<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Ambil URL siaran langsung (HLS) kamera dari Tuya Cloud API.
 * Permintaan ditandatangani manual (HMAC-SHA256), tanpa SDK.
 */
class TuyaCameraService
{
    // Endpoint per data center Tuya
    private const HOSTS = [
        'sg' => 'https://openapi-sg.iotbing.com',
        'us' => 'https://openapi.tuyaus.com',
        'ueaz' => 'https://openapi-ueaz.tuyaus.com',
        'eu' => 'https://openapi.tuyaeu.com',
        'weaz' => 'https://openapi-weaz.tuyaeu.com',
        'in' => 'https://openapi.tuyain.com',
        'cn' => 'https://openapi.tuyacn.com',
    ];

    private const EMPTY_BODY_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

    public function isConfigured(): bool
    {
        return filled(config('services.tuya.access_id'))
            && filled(config('services.tuya.access_secret'))
            && filled(config('services.tuya.device_id'));
    }

    /**
     * Minta URL siaran baru untuk kamera (berlaku beberapa menit).
     * "flv" jedanya ±1–3 detik; "hls" ±10 detik tetapi didukung semua browser.
     */
    public function allocateStreamUrl(string $type = 'hls'): string
    {
        $deviceId = config('services.tuya.device_id');
        $result = $this->request('POST', "/v1.0/devices/{$deviceId}/stream/actions/allocate", ['type' => $type]);

        if (empty($result['url'])) {
            throw new RuntimeException('Tuya tidak mengembalikan URL siaran.');
        }

        return $result['url'];
    }

    /**
     * Ringkasan status kamera: online, baterai, sumber daya.
     * Saat kamera offline, nilai DP adalah laporan terakhirnya (lihat "dilaporkan").
     * Di-cache STATUS_CACHE_SECONDS dan dipakai bersama semua penonton, supaya
     * pemakaian tetap di bawah kuota gratis Tuya (±26 ribu panggilan/bulan;
     * tiap pembaruan = 2 panggilan).
     */
    public const STATUS_CACHE_SECONDS = 300;

    public function deviceStatus(): array
    {
        return Cache::remember('tuya_device_status', self::STATUS_CACHE_SECONDS, function () {
            $deviceId = config('services.tuya.device_id');
            $device = $this->request('GET', "/v1.0/devices/{$deviceId}");
            $dp = collect($this->request('GET', "/v2.0/cloud/thing/{$deviceId}/shadow/properties")['properties'] ?? [])
                ->keyBy('code');

            $baterai = $dp->get('wireless_electricity');

            return [
                'online' => (bool) ($device['online'] ?? false),
                'baterai' => $baterai['value'] ?? null, // 0–100 (%)
                'baterai_lemah' => $dp->get('wireless_lowpower')['value'] ?? 10, // ambang alarm (%)
                'mengisi_daya' => ($dp->get('wireless_powermode')['value'] ?? null) === '1', // 1 = dicolok / sedang diisi
                'dilaporkan' => isset($baterai['time']) ? intdiv($baterai['time'], 1000) : null, // unix detik
                'diperiksa' => time(), // kapan status ini diambil dari Tuya
            ];
        });
    }

    /**
     * Gerakkan kamera (pan/tilt). Arah sesuai DP ptz_control:
     * 0 atas, 1 kanan atas, 2 kanan, 3 kanan bawah, 4 bawah, 5 kiri bawah, 6 kiri, 7 kiri atas.
     */
    public function ptzMove(int $direction): void
    {
        $this->issueProperties(['ptz_control' => (string) $direction]);
    }

    public function ptzStop(): void
    {
        $this->issueProperties(['ptz_stop' => true]);
    }
    private function issueProperties(array $properties): void
    {
        $deviceId = config('services.tuya.device_id');
        // API ini meminta "properties" berupa string JSON, bukan objek
        $this->request('POST', "/v2.0/cloud/thing/{$deviceId}/shadow/properties/issue", [
            'properties' => json_encode($properties),
        ]);
    }

    private function host(): string
    {
        $region = strtolower((string) config('services.tuya.region', 'sg'));

        return self::HOSTS[$region] ?? throw new RuntimeException("TUYA_REGION tidak dikenal: {$region}");
    }

    /**
     * Access token di-cache sedikit lebih singkat dari masa berlakunya.
     */
    private function accessToken(): string
    {
        $cached = Cache::get('tuya_access_token');
        if ($cached) {
            return $cached;
        }

        $result = $this->send('GET', '/v1.0/token?grant_type=1', null, null);
        $token = $result['access_token'] ?? null;

        if (! $token) {
            throw new RuntimeException('Gagal mengambil access token Tuya.');
        }

        Cache::put('tuya_access_token', $token, max(60, (int) ($result['expire_time'] ?? 7200) - 120));

        return $token;
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        try {
            return $this->send($method, $path, $body, $this->accessToken());
        } catch (RuntimeException $e) {
            // Token kedaluwarsa/tidak valid: buang cache lalu coba sekali lagi
            if (str_contains($e->getMessage(), '1010') || str_contains($e->getMessage(), '1011')) {
                Cache::forget('tuya_access_token');

                return $this->send($method, $path, $body, $this->accessToken());
            }
            throw $e;
        }
    }

    private function send(string $method, string $path, ?array $body, ?string $accessToken): array
    {
        $clientId = config('services.tuya.access_id');
        $secret = config('services.tuya.access_secret');
        $t = (string) round(microtime(true) * 1000);
        $nonce = (string) Str::uuid();
        $json = $body === null ? '' : json_encode($body);

        $stringToSign = $method . "\n"
            . ($json === '' ? self::EMPTY_BODY_HASH : hash('sha256', $json)) . "\n"
            . "\n"
            . $path;

        $sign = strtoupper(hash_hmac('sha256', $clientId . ($accessToken ?? '') . $t . $nonce . $stringToSign, $secret));

        $headers = [
            'client_id' => $clientId,
            'sign' => $sign,
            't' => $t,
            'nonce' => $nonce,
            'sign_method' => 'HMAC-SHA256',
        ];
        if ($accessToken) {
            $headers['access_token'] = $accessToken;
        }

        $http = Http::withHeaders($headers)->timeout(15);
        $url = $this->host() . $path;

        try {
            $response = $method === 'POST'
                ? $http->withBody($json, 'application/json')->post($url)
                : $http->get($url);
        } catch (ConnectionException $e) {
            // Jaringan/DNS gagal: jadikan RuntimeException agar ditangani seperti error Tuya lain
            Log::warning('Tuya API tidak terjangkau', ['path' => $path, 'msg' => $e->getMessage()]);
            throw new RuntimeException('Server Tuya tidak bisa dihubungi. Periksa koneksi internet server.', 0, $e);
        }

        $data = $response->json();

        if (! $response->successful() || ! ($data['success'] ?? false)) {
            Log::warning('Tuya API gagal', ['path' => $path, 'code' => $data['code'] ?? $response->status(), 'msg' => $data['msg'] ?? null]);
            throw new RuntimeException('Tuya API: ' . ($data['msg'] ?? 'HTTP ' . $response->status()) . ' (' . ($data['code'] ?? '-') . ')');
        }

        return is_array($data['result'] ?? null) ? $data['result'] : [];
    }
}

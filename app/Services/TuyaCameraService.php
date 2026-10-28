<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Kamera CCTV lewat Tuya Cloud API: daftar & status kamera, siaran langsung, baterai.
 * Permintaan ditandatangani manual (HMAC-SHA256), tanpa SDK. Kamera mana yang
 * dipakai ditentukan oleh tabel `cctvs` (Kelola CCTV), bukan .env.
 */
class TuyaCameraService
{
    /**
     * Status di-cache sekian detik dan dipakai bersama semua penonton, supaya
     * pemakaian tetap di bawah kuota gratis Tuya (±26 ribu panggilan/bulan).
     */
    public const STATUS_CACHE_SECONDS = 300;

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
            && filled(config('services.tuya.access_secret'));
    }

    /**
     * Semua kamera yang tertaut ke proyek Tuya (akun Smart Life yang di-link),
     * dikunci per Device ID: ['id', 'nama', 'online']. Satu panggilan API untuk
     * semua kamera, jadi dipakai juga sebagai status online (STATUS_CACHE_SECONDS).
     * local_key dsb. sengaja tidak ikut disimpan.
     */
    public function devices(): array
    {
        $daftar = Cache::remember('tuya_devices', self::STATUS_CACHE_SECONDS, function () {
            $hasil = [];
            $lastRowKey = null;

            do {
                $query = '/v1.0/iot-01/associated-users/devices?size=50' . ($lastRowKey ? '&last_row_key=' . urlencode($lastRowKey) : '');
                $page = $this->request('GET', $query);

                foreach ($page['devices'] ?? [] as $d) {
                    $hasil[$d['id']] = [
                        'id' => $d['id'],
                        'nama' => $d['name'] ?? $d['id'],
                        'online' => (bool) ($d['online'] ?? false),
                    ];
                }
                $lastRowKey = $page['last_row_key'] ?? null;
            } while (($page['has_more'] ?? false) && $lastRowKey);

            return ['data' => $hasil, 'diperiksa' => time()];
        });

        return $daftar;
    }

    /**
     * Minta URL siaran baru untuk kamera (berlaku beberapa menit).
     * "flv" jedanya ±1–3 detik; "hls" ±10 detik (hanya bisa diputar native di Safari).
     */
    public function allocateStreamUrl(string $deviceId, string $type = 'flv'): string
    {
        $result = $this->request('POST', "/v1.0/devices/{$deviceId}/stream/actions/allocate", ['type' => $type]);

        if (empty($result['url'])) {
            throw new RuntimeException('Tuya tidak mengembalikan URL siaran.');
        }

        return $result['url'];
    }

    /**
     * Status satu kamera: online (dari devices()) + baterai & sumber daya.
     * Saat kamera offline, nilai DP adalah laporan terakhirnya (lihat "dilaporkan").
     * Baterai hanya diambil untuk kamera yang sedang ditonton (1 panggilan per
     * kamera per STATUS_CACHE_SECONDS).
     */
    public function deviceStatus(string $deviceId): array
    {
        $daftar = $this->devices();

        $dp = Cache::remember('tuya_dp_' . $deviceId, self::STATUS_CACHE_SECONDS, function () use ($deviceId) {
            return collect($this->request('GET', "/v2.0/cloud/thing/{$deviceId}/shadow/properties")['properties'] ?? [])
                ->keyBy('code')
                ->only(['wireless_electricity', 'wireless_lowpower', 'wireless_powermode'])
                ->all();
        });

        $baterai = $dp['wireless_electricity'] ?? null;

        return [
            'online' => $daftar['data'][$deviceId]['online'] ?? false,
            'terdaftar' => isset($daftar['data'][$deviceId]), // masih tertaut ke proyek Tuya?
            'baterai' => $baterai['value'] ?? null, // 0–100 (%)
            'baterai_lemah' => $dp['wireless_lowpower']['value'] ?? 10, // ambang alarm (%)
            'mengisi_daya' => ($dp['wireless_powermode']['value'] ?? null) === '1', // 1 = dicolok / sedang diisi
            'dilaporkan' => isset($baterai['time']) ? intdiv($baterai['time'], 1000) : null, // unix detik
            'diperiksa' => $daftar['diperiksa'], // kapan status online diambil dari Tuya
        ];
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

        // Tuya menandatangani URL dengan parameter query terurut abjad; tanpa ini
        // permintaan dengan >1 parameter ditolak "sign invalid" (1004).
        if (str_contains($path, '?')) {
            [$jalur, $query] = explode('?', $path, 2);
            $param = [];
            foreach (explode('&', $query) as $pasangan) {
                [$k, $v] = array_pad(explode('=', $pasangan, 2), 2, '');
                $param[$k] = $v;
            }
            ksort($param, SORT_STRING);
            $path = $jalur . '?' . implode('&', array_map(fn ($k, $v) => $k . '=' . $v, array_keys($param), $param));
        }

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

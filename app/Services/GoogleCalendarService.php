<?php

namespace App\Services;

use App\Models\AgendaRapat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleCalendarService
{
    private const TOKEN_URI = 'https://oauth2.googleapis.com/token';
    private const API_BASE = 'https://www.googleapis.com/calendar/v3';
    private const SCOPE = 'https://www.googleapis.com/auth/calendar';

    private ?array $credentials = null;

    /**
     * Load service account JSON credentials dari path di config.
     */
    private function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $path = base_path(config('services.google_calendar.credentials_path'));

        if (!file_exists($path)) {
            throw new RuntimeException("Google Calendar service account file tidak ditemukan: {$path}");
        }

        return $this->credentials = json_decode(file_get_contents($path), true);
    }

    /**
     * Buat JWT (RS256) yang ditandatangani dengan private key service account,
     * lalu tukar dengan access token OAuth2 (di-cache selama ~55 menit).
     */
    private function accessToken(): string
    {
        return Cache::remember('google_calendar_access_token', 3300, function () {
            $creds = $this->credentials();
            $now = time();

            $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->base64UrlEncode(json_encode([
                'iss'   => $creds['client_email'],
                'scope' => self::SCOPE,
                'aud'   => self::TOKEN_URI,
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]));

            $signatureInput = "{$header}.{$claims}";
            openssl_sign($signatureInput, $signature, $creds['private_key'], 'sha256WithRSAEncryption');
            $jwt = $signatureInput . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()->post(self::TOKEN_URI, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('Google Calendar: gagal ambil access token', ['response' => $response->body()]);
                throw new RuntimeException('Gagal autentikasi ke Google Calendar API.');
            }

            return $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function calendarId(): string
    {
        return config('services.google_calendar.calendar_id');
    }

    /**
     * Bangun payload event Google Calendar dari data Agenda Rapat.
     * Tanpa tanggal_agenda, agenda tidak bisa disinkronkan (Google Calendar wajib punya start/end).
     * Tanpa waktu, event dibuat sebagai all-day event.
     */
    public function buildEventPayload(AgendaRapat $agenda): ?array
    {
        if (!$agenda->tanggal_agenda) {
            return null;
        }

        $tanggal = $agenda->tanggal_agenda->format('Y-m-d');
        $deskripsi = trim(($agenda->ruangan ? "Ruangan: {$agenda->ruangan}\n" : '') . ($agenda->keterangan ?? ''));

        $payload = [
            'summary'     => $agenda->nama_agenda,
            'location'    => $agenda->ruangan,
            'description' => $deskripsi ?: null,
        ];

        if ($agenda->waktu) {
            $mulai = \Illuminate\Support\Carbon::parse($agenda->tanggal_agenda->format('Y-m-d') . ' ' . $agenda->waktu, 'Asia/Makassar');
            $selesai = $mulai->copy()->addHour();

            $payload['start'] = ['dateTime' => $mulai->toRfc3339String(), 'timeZone' => 'Asia/Makassar'];
            $payload['end']   = ['dateTime' => $selesai->toRfc3339String(), 'timeZone' => 'Asia/Makassar'];
        } else {
            $payload['start'] = ['date' => $tanggal];
            $payload['end']   = ['date' => $tanggal];
        }

        return $payload;
    }

    /**
     * Sinkronkan satu AgendaRapat ke Google Calendar (create/update otomatis),
     * lalu simpan google_event_id ke row jika ada perubahan. Aman dipanggil meski
     * kredensial/config belum siap — kegagalan hanya di-log, tidak melempar exception.
     */
    public function syncAgenda(AgendaRapat $agenda): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $payload = $this->buildEventPayload($agenda);

        if (!$payload) {
            // Tidak ada tanggal: kalau sebelumnya pernah tersinkron, hapus event lama.
            if ($agenda->google_event_id) {
                $this->deleteEvent($agenda->google_event_id);
                $agenda->update(['google_event_id' => null]);
            }
            return;
        }

        if ($agenda->google_event_id) {
            $ok = $this->updateEvent($agenda->google_event_id, $payload);
            if (!$ok) {
                // Event mungkin sudah terhapus manual di Google Calendar; buat ulang.
                $newId = $this->createEvent($payload);
                if ($newId) {
                    $agenda->update(['google_event_id' => $newId]);
                }
            }
            return;
        }

        $eventId = $this->createEvent($payload);
        if ($eventId) {
            $agenda->update(['google_event_id' => $eventId]);
        }
    }

    /**
     * Hapus event Google Calendar terkait AgendaRapat (dipanggil sebelum delete row).
     */
    public function removeAgenda(AgendaRapat $agenda): void
    {
        if ($this->isConfigured() && $agenda->google_event_id) {
            $this->deleteEvent($agenda->google_event_id);
        }
    }

    private function isConfigured(): bool
    {
        return (bool) config('services.google_calendar.calendar_id')
            && file_exists(base_path(config('services.google_calendar.credentials_path')));
    }

    /**
     * Buat event baru di Google Calendar. Return event ID Google, atau null jika gagal.
     */
    public function createEvent(array $eventData): ?string
    {
        try {
            $response = Http::withToken($this->accessToken())
                ->post(self::API_BASE . '/calendars/' . urlencode($this->calendarId()) . '/events', $eventData);

            if (!$response->successful()) {
                Log::warning('Google Calendar: gagal membuat event', ['response' => $response->body()]);
                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('Google Calendar: exception saat membuat event', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Perbarui event yang sudah ada di Google Calendar berdasarkan event ID.
     */
    public function updateEvent(string $googleEventId, array $eventData): bool
    {
        try {
            $response = Http::withToken($this->accessToken())
                ->put(self::API_BASE . '/calendars/' . urlencode($this->calendarId()) . '/events/' . urlencode($googleEventId), $eventData);

            if (!$response->successful()) {
                Log::warning('Google Calendar: gagal memperbarui event', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Calendar: exception saat memperbarui event', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Hapus event dari Google Calendar berdasarkan event ID.
     */
    public function deleteEvent(string $googleEventId): bool
    {
        try {
            $response = Http::withToken($this->accessToken())
                ->delete(self::API_BASE . '/calendars/' . urlencode($this->calendarId()) . '/events/' . urlencode($googleEventId));

            // 410 Gone = event sudah terhapus sebelumnya di Google Calendar; anggap sukses.
            if (!$response->successful() && $response->status() !== 410 && $response->status() !== 404) {
                Log::warning('Google Calendar: gagal menghapus event', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Calendar: exception saat menghapus event', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QidApiService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $appsId;

    /** Cache key untuk menyimpan token JWT */
    private const TOKEN_CACHE_KEY = 'qidapi_access_token';

    /** Durasi cache token (menit) — sedikit di bawah masa berlaku token aslinya */
    private const TOKEN_TTL_MINUTES = 55;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('qidapi.base_url'), '/');
        $this->username = config('qidapi.username');
        $this->password = config('qidapi.password');
        $this->appsId   = config('qidapi.apps_id');
    }

    // -------------------------------------------------------------------------
    // Autentikasi
    // -------------------------------------------------------------------------

    /**
     * Login ke QidApi dan kembalikan token JWT.
     * Token di-cache otomatis agar tidak login ulang di setiap request.
     *
     * @return string|null  Token JWT, atau null jika gagal
     */
    public function login(): ?string
    {
        try {
            $response = Http::withHeaders([
                'Accept'       => 'text/plain',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/authorization/login", [
                'username' => $this->username,
                'password' => $this->password,
                'appsId'   => $this->appsId,
            ]);

            if ($response->successful()) {
                $json = $response->json();

                // Struktur respons: { error, message, data: { token, username, fullname, ... } }
                // Atau fallback ke flat: { token } / { accessToken } / plain-text
                $token = $json['data']['token']
                    ?? $json['data']['accessToken']
                    ?? $json['data']['access_token']
                    ?? $json['token']
                    ?? $json['accessToken']
                    ?? $json['access_token']
                    ?? null;

                // Fallback: plain-text response
                if (!$token) {
                    $body = trim($response->body());
                    if (strlen($body) > 10 && !str_starts_with($body, '{')) {
                        $token = $body;
                    }
                }

                if ($token) {
                    // Hitung TTL dari field 'expiresIn' jika tersedia (detik)
                    $expiresIn = $json['data']['expiresIn'] ?? null;
                    $ttl = $expiresIn
                        ? now()->addSeconds(max(0, (int) $expiresIn - 300)) // kurangi 5 menit sbg buffer
                        : now()->addMinutes(self::TOKEN_TTL_MINUTES);

                    Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

                    // Cache info user untuk keperluan lain
                    if (!empty($json['data'])) {
                        Cache::put('qidapi_user_info', $json['data'], $ttl);
                    }

                    Log::info('QidApi login successful', [
                        'user'    => $json['data']['username'] ?? '-',
                        'expires' => $json['data']['expires'] ?? '-',
                    ]);

                    return $token;
                }
            }

            Log::error('QidApi login failed', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('QidApi login exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Ambil token dari cache; login ulang jika belum ada atau sudah expired.
     */
    public function getToken(): ?string
    {
        if (Cache::has(self::TOKEN_CACHE_KEY)) {
            return Cache::get(self::TOKEN_CACHE_KEY);
        }

        return $this->login();
    }

    /**
     * Hapus token dari cache (force logout / invalidasi).
     */
    public function logout(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
        Log::info('QidApi token cleared');
    }

    /**
     * Cek apakah konfigurasi sudah terisi.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl)
            && !empty($this->username)
            && !empty($this->password)
            && !empty($this->appsId);
    }

    // -------------------------------------------------------------------------
    // HTTP Helpers (dengan auto-retry login sekali jika 401)
    // -------------------------------------------------------------------------

    /**
     * Buat header Authorization dengan Bearer token.
     */
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Kirim GET request ke endpoint QidApi.
     *
     * @param string $endpoint  Path endpoint (contoh: '/master/products')
     * @param array  $query     Query string parameters
     * @return array|null
     */
    public function get(string $endpoint, array $query = []): ?array
    {
        return $this->request('GET', $endpoint, $query);
    }

    /**
     * Kirim POST request ke endpoint QidApi.
     *
     * @param string $endpoint  Path endpoint
     * @param array  $payload   Body JSON
     * @return array|null
     */
    public function post(string $endpoint, array $payload = []): ?array
    {
        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Kirim PUT request ke endpoint QidApi.
     *
     * @param string $endpoint  Path endpoint
     * @param array  $payload   Body JSON
     * @return array|null
     */
    public function put(string $endpoint, array $payload = []): ?array
    {
        return $this->request('PUT', $endpoint, $payload);
    }

    /**
     * Kirim DELETE request ke endpoint QidApi.
     *
     * @param string $endpoint  Path endpoint
     * @param array  $payload   Body JSON (opsional)
     * @return array|null
     */
    public function delete(string $endpoint, array $payload = []): ?array
    {
        return $this->request('DELETE', $endpoint, $payload);
    }

    /**
     * Method internal untuk mengirim request dengan auto-retry jika token expired (401).
     *
     * @param string $method   HTTP method (GET|POST|PUT|DELETE)
     * @param string $endpoint Path endpoint
     * @param array  $data     Query / body payload
     * @param bool   $retry    Apakah ini percobaan ulang setelah refresh token
     * @return array|null
     */
    protected function request(string $method, string $endpoint, array $data = [], bool $retry = false): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('QidApi not configured. Please check QIDAPI_* env variables.');
            return null;
        }

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        try {
            $http = Http::withHeaders($this->authHeaders());

            $response = match (strtoupper($method)) {
                'GET'    => $http->get($url, $data),
                'POST'   => $http->post($url, $data),
                'PUT'    => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            // Jika 401, coba refresh token sekali lalu ulangi request
            if ($response->status() === 401 && !$retry) {
                Log::warning('QidApi token expired, re-authenticating...');
                $this->logout();
                $this->login();
                return $this->request($method, $endpoint, $data, true);
            }

            if ($response->successful()) {
                return $response->json() ?? ['raw' => $response->body()];
            }

            Log::error('QidApi request error', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('QidApi request exception', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'message'  => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Ambil info user yang di-cache saat login (username, fullname, position, dll).
     *
     * @return array|null
     */
    public function getUserInfo(): ?array
    {
        return Cache::get('qidapi_user_info');
    }
}

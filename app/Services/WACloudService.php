<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WACloudService
{
    private $baseUrl = 'https://app.wacloud.id/api/v1';
    private $apiKey;
    private $deviceId;
    private $authToken;

    public function __construct()
    {
        $this->apiKey = env('WACLOUD_API_KEY');
        $this->deviceId = env('WACLOUD_DEVICE_ID');
        $this->authToken = env('WACLOUD_AUTH_TOKEN');
    }

    /**
     * Check if WACloud is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->deviceId) && !empty($this->authToken);
    }

    /**
     * Send text message via WACloud
     * 
     * @param string $to Phone number (format: 6281234567890)
     * @param string $message Message text
     * @return array
     */
    public function sendTextMessage(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            Log::error('WACloud not configured. Please set API key, auth token, and device ID in settings.');
            return ['success' => false, 'message' => 'WACloud not configured'];
        }

        try {
            $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Authorization' => 'Bearer ' . $this->authToken,
            ])
            ->timeout(10)
            ->connectTimeout(5)
            ->post("{$this->baseUrl}/messages", [
                'device_id' => $this->deviceId,
                'to' => $to,
                'message_type' => 'text',
                'text' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WACloud message sent successfully', [
                    'to' => $to,
                    'response' => $response->json(),
                ]);
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WACloud API error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return ['success' => false, 'message' => 'API Error: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('WACloud exception', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send image message via WACloud
     * 
     * @param string $to Phone number
     * @param string $imageUrl Image URL
     * @param string|null $caption Caption text (optional)
     * @return array
     */
    public function sendImageMessage(string $to, string $imageUrl, ?string $caption = null): array
    {
        if (!$this->isConfigured()) {
            Log::error('WACloud not configured. Please set API key, auth token, and device ID in settings.');
            return ['success' => false, 'message' => 'WACloud not configured'];
        }

        try {
            $payload = [
                'device_id' => $this->deviceId,
                'to' => $to,
                'message_type' => 'image',
                'image_url' => $imageUrl,
            ];

            if ($caption) {
                $payload['text'] = $caption;
            }

            $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Authorization' => 'Bearer ' . $this->authToken,
            ])->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                Log::info('WACloud image message sent successfully', [
                    'to' => $to,
                    'response' => $response->json(),
                ]);
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WACloud API error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return ['success' => false, 'message' => 'API Error: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('WACloud exception', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format phone number to WhatsApp format (remove +, spaces, dashes)
     */
    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        $phone = ltrim($phone, '+');
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}

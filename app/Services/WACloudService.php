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

    public function __construct()
    {
        $this->apiKey = Setting::get('wacloud_api_key');
        $this->deviceId = Setting::get('wacloud_device_id');
    }

    /**
     * Check if WACloud is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->deviceId);
    }

    /**
     * Send text message via WACloud
     * 
     * @param string $to Phone number (format: 6281234567890)
     * @param string $message Message text
     * @return array|null
     */
    public function sendTextMessage(string $to, string $message): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('WACloud not configured. Please set API key and device ID in settings.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/messages", [
                'device_id' => $this->deviceId,
                'to' => $to,
                'message_type' => 'text',
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    Log::info('WACloud message sent successfully', [
                        'to' => $to,
                        'message_id' => $data['data']['message_id'] ?? null,
                    ]);
                    return $data['data'];
                }
            }

            Log::error('WACloud API error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud exception', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);
            return null;
        }
    }

    /**
     * Send image message via WACloud
     * 
     * @param string $to Phone number
     * @param string $imageUrl Image URL
     * @param string|null $caption Caption text (optional)
     * @return array|null
     */
    public function sendImageMessage(string $to, string $imageUrl, ?string $caption = null): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('WACloud not configured. Please set API key and device ID in settings.');
            return null;
        }

        try {
            $payload = [
                'device_id' => $this->deviceId,
                'to' => $to,
                'message_type' => 'image',
                'image' => $imageUrl,
            ];

            if ($caption) {
                $payload['caption'] = $caption;
            }

            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    Log::info('WACloud image message sent successfully', [
                        'to' => $to,
                        'message_id' => $data['data']['message_id'] ?? null,
                    ]);
                    return $data['data'];
                }
            }

            Log::error('WACloud API error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud exception', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);
            return null;
        }
    }

    /**
     * Send document message via WACloud
     * 
     * @param string $to Phone number
     * @param string $documentUrl Document URL
     * @param string|null $filename Filename (optional)
     * @return array|null
     */
    public function sendDocumentMessage(string $to, string $documentUrl, ?string $filename = null): ?array
    {
        if (!$this->isConfigured()) {
            Log::error('WACloud not configured. Please set API key and device ID in settings.');
            return null;
        }

        try {
            $payload = [
                'device_id' => $this->deviceId,
                'to' => $to,
                'message_type' => 'document',
                'document' => $documentUrl,
            ];

            if ($filename) {
                $payload['filename'] = $filename;
            }

            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    Log::info('WACloud document message sent successfully', [
                        'to' => $to,
                        'message_id' => $data['data']['message_id'] ?? null,
                    ]);
                    return $data['data'];
                }
            }

            Log::error('WACloud API error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud exception', [
                'message' => $e->getMessage(),
                'to' => $to,
            ]);
            return null;
        }
    }

    /**
     * Get list of devices
     * 
     * @return array|null
     */
    public function getDevices(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/devices");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    return $data['data'] ?? [];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud get devices exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get device details
     * 
     * @param string|null $deviceId Device ID (optional, uses configured device if not provided)
     * @return array|null
     */
    public function getDevice(?string $deviceId = null): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $deviceId = $deviceId ?? $this->deviceId;

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/devices/{$deviceId}");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    return $data['data'] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud get device exception', [
                'message' => $e->getMessage(),
                'device_id' => $deviceId,
            ]);
            return null;
        }
    }

    /**
     * Check if phone number exists on WhatsApp
     * 
     * @param string $phone Phone number (format: 6281234567890)
     * @return array|null Returns array with 'numberExists' and 'chatId' keys
     */
    public function checkPhoneExists(string $phone): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/devices/{$this->deviceId}/contacts/check-exists", [
                'phone' => $phone,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success']) {
                    return $data['data'] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud check phone exists exception', [
                'message' => $e->getMessage(),
                'phone' => $phone,
            ]);
            return null;
        }
    }

    /**
     * Get account information and quota
     * 
     * @return array|null Returns array with account info and quota
     */
    public function getAccount(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/account");

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success']) {
                    $accountData = $data['data'] ?? null;
                    
                    // Extract quota information from nested structure
                    if ($accountData && isset($accountData['quota']) && is_array($accountData['quota'])) {
                        $quota = $accountData['quota'];
                        // Format quota data for easier access
                        $accountData['quota_balance'] = $quota['balance'] ?? null;
                        $accountData['quota_text'] = $quota['text_quota'] ?? null;
                        $accountData['quota_multimedia'] = $quota['multimedia_quota'] ?? null;
                        $accountData['quota_free_text'] = $quota['free_text_quota'] ?? null;
                        $accountData['quota_total_text'] = $quota['total_text_quota'] ?? null;
                        $accountData['quota'] = $quota['balance'] ?? null; // For backward compatibility
                    }
                    
                    return $accountData;
                }
            }

            Log::error('WACloud get account error', [
                'status' => $response->status(),
                'response' => $response->json(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('WACloud get account exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Format phone number to WhatsApp format (remove +, spaces, dashes)
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters except leading +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Remove leading + if exists
        $phone = ltrim($phone, '+');
        
        // If starts with 0, replace with country code 62 (Indonesia)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // If doesn't start with country code, assume Indonesia (62)
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}


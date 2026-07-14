<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $apiVersion;

    public function __construct()
    {
        $this->token = env('META_WA_TOKEN');
        $this->phoneNumberId = env('META_WA_PHONE_NUMBER_ID');
        $this->apiVersion = env('META_WA_API_VERSION', 'v25.0');
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneNumberId);
    }

    public function sendText(string $phone, string $message): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Meta WhatsApp is not configured',
            ];
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $this->formatPhoneNumber($phone),
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Meta WhatsApp text failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'phone' => $phone,
            ]);

            return [
                'success' => false,
                'message' => 'API request failed: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function sendTemplate(string $phone, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Meta WhatsApp is not configured',
            ];
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
        
        $templateData = [
            'name' => $templateName,
            'language' => [
                'code' => $languageCode,
            ],
        ];
        
        if (!empty($components)) {
            $templateData['components'] = $components;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phone),
                'type' => 'template',
                'template' => $templateData,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Meta WhatsApp template failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'phone' => $phone,
            ]);

            return [
                'success' => false,
                'message' => 'API request failed: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QadWhatsAppService
{
    /**
     * @var QidApiService
     */
    protected $qidApiService;

    /**
     * QadWhatsAppService constructor.
     * 
     * @param QidApiService $qidApiService
     */
    public function __construct(QidApiService $qidApiService)
    {
        $this->qidApiService = $qidApiService;
    }

    /**
     * Send WhatsApp text message via QAD API.
     * 
     * @param string $phone   Recipient phone number (e.g. 089699935552)
     * @param string $message Message content
     * @return array
     */
    public function sendText(string $phone, string $message): array
    {
        $baseUrl = rtrim(config('qidapi.base_url', 'https://development-qadwebapi.rasagroupoffice.com'), '/');
        $endpoint = '/api/system/notification/send-text';
        $url = $baseUrl . $endpoint;

        // Get token from QidApiService (handles caching & auto-login)
        $token = $this->qidApiService->getToken();

        if (!$token) {
            Log::error('QadWhatsAppService: Failed to obtain authentication token from QidApiService');
            return [
                'success' => false,
                'message' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'text/plain',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->timeout(10) // Total request timeout
            ->connectTimeout(5) // Connection timeout
            ->post($url, [
                'phone'   => $this->formatPhoneNumber($phone),
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('QadWhatsAppService: Message sent successfully', [
                    'phone' => $phone,
                ]);

                return [
                    'success' => true,
                    'body'    => $response->body(),
                ];
            }

            Log::error('QadWhatsAppService: API request failed', [
                'status'   => $response->status(),
                'response' => $response->body(),
                'phone'    => $phone,
            ]);

            return [
                'success' => false,
                'status'   => $response->status(),
                'message'  => $response->body() ?: 'Unknown error from API',
            ];

        } catch (\Exception $e) {
            Log::error('QadWhatsAppService: Exception occurred', [
                'message' => $e->getMessage(),
                'phone'   => $phone,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to WhatsApp format (e.g. 6281234567890).
     * 
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with country code 62 (Indonesia)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        // If doesn't start with country code, assume Indonesia (62)
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}

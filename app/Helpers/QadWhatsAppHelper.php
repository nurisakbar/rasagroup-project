<?php

namespace App\Helpers;

use App\Services\QadWhatsAppService;
use App\Services\QidApiService;
use Illuminate\Support\Facades\Log;

class QadWhatsAppHelper
{
    /**
     * Send WhatsApp text message using QAD API.
     * 
     * @param string $phone   Recipient phone number
     * @param string $message Message content
     * @return array
     */
    public static function sendText(string $phone, string $message): array
    {
        try {
            // Instantiate service manually or via app container
            // Since QadWhatsAppService needs QidApiService, we let the container handle it
            $service = app(QadWhatsAppService::class);
            
            return $service->sendText($phone, $message);
        } catch (\Exception $e) {
            Log::error('QadWhatsAppHelper: Failed to send text', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $waCode;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $waCode)
    {
        $this->user = $user;
        $this->waCode = $waCode;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\MetaWhatsAppService $metaService): void
    {
        try {
            $templateName = env('META_WA_OTP_TEMPLATE', 'otp_register');
            $languageCode = env('META_WA_TEMPLATE_LANG', 'en');
            
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $this->waCode
                        ]
                    ]
                ],
                [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $this->waCode
                        ]
                    ]
                ]
            ];
            
            $result = $metaService->sendTemplate($this->user->phone, $templateName, $languageCode, $components);
            
            if ($result['success']) {
                Log::info("WhatsApp OTP template {$templateName} sent to {$this->user->phone} via Meta API");
            } else {
                Log::error("Failed to send WhatsApp OTP template {$templateName} to {$this->user->phone} via Meta API: " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp verification to {$this->user->phone}: " . $e->getMessage());
            // Re-throw to allow retries if needed
            throw $e;
        }
    }
}

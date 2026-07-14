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
            // SEMENTARA KITA GUNAKAN TEKS BIASA SAMPAI TEMPLATE OTP DIACTIVATE OLEH META
            $message = "Halo {$this->user->name}, kode verifikasi Anda adalah: {$this->waCode}. Segera masukkan kode ini untuk mengaktifkan akun Anda.";
            
            $result = $metaService->sendText($this->user->phone, $message);
            
            if ($result['success']) {
                Log::info("WhatsApp OTP verification sent to {$this->user->phone} via Meta API");
            } else {
                Log::error("Failed to send WhatsApp OTP to {$this->user->phone} via Meta API: " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp verification to {$this->user->phone}: " . $e->getMessage());
            // Re-throw to allow retries if needed
            throw $e;
        }
    }
}

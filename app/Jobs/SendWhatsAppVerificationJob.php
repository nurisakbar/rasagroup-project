<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\QadWhatsAppService;
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
    public function handle(QadWhatsAppService $whatsappService): void
    {
        try {
            $message = "Halo {$this->user->name}, selamat datang di Multi Citra Rasa Marketplace! Kode verifikasi Anda adalah: {$this->waCode}. Segera masukkan kode ini untuk mengaktifkan akun Anda.";
            $whatsappService->sendText($this->user->phone, $message);
            
            Log::info("WhatsApp verification sent to {$this->user->phone}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp verification to {$this->user->phone}: " . $e->getMessage());
            // Re-throw to allow retries if needed
            throw $e;
        }
    }
}

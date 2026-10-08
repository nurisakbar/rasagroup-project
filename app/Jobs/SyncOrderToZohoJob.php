<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ZohoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderToZohoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    
    // Automatically retry if it fails
    public $tries = 3;
    public $backoff = [30, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(ZohoService $zohoService): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            Log::warning("SyncOrderToZohoJob: Order {$this->orderId} not found");
            return;
        }

        // Only sync if status is paid (or logic allows it) and not already synced
        if ($order->zoho_salesorder_id && $order->zoho_invoice_id && $order->zoho_payment_id) {
            Log::info("SyncOrderToZohoJob: Order {$order->order_number} is already fully synced to Zoho.");
            return;
        }

        $zohoService->createOrderToPaymentFlow($order);
    }
}

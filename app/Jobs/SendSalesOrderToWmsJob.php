<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSalesOrderToWmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->order->loadMissing('items.product');

        // Check if API key and URL are configured
        $apiUrl = config('services.wms.api_url');
        $apiKey = config('services.wms.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            Log::warning('WMS API is not configured. Skipping Sales Order Sync.', ['order_number' => $this->order->order_number]);
            return;
        }

        $itemsPayload = [];

        foreach ($this->order->items as $item) {
            $batchesPayload = [];
            $totalBatchQty = 0;

            if (!empty($item->allocated_batches) && is_array($item->allocated_batches)) {
                foreach ($item->allocated_batches as $batch) {
                    $batchesPayload[] = [
                        'batch_number' => $batch['lot_serial'] ?? '',
                        'location_code' => $this->order->source_qad_location_code ?? '',
                        'quantity' => (float) ($batch['qty'] ?? 0),
                    ];
                    $totalBatchQty += (float) ($batch['qty'] ?? 0);
                }
            }

            if ($totalBatchQty != $item->quantity) {
                Log::warning('WMS Sync: Item quantity does not match allocated batches.', [
                    'order_number' => $this->order->order_number,
                    'item_code' => $item->product->code,
                    'item_quantity' => $item->quantity,
                    'allocated_total' => $totalBatchQty
                ]);
            }

            $itemsPayload[] = [
                'item_code' => $item->product->code,
                'quantity' => (float) $item->quantity,
                'batches' => $batchesPayload,
            ];
        }

        $payload = [
            'so_number' => $this->order->order_number,
            'items' => $itemsPayload,
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->post(rtrim($apiUrl, '/') . '/sales-orders', $payload);

            $this->order->wms_so_synced_at = now();
            
            if ($response->successful()) {
                $responseData = $response->json();
                $status = $responseData['data']['status'] ?? 'QUEUED';
                
                if ($response->status() === 200) {
                    $status = $responseData['data']['status'] ?? 'SUCCESS';
                }

                $this->order->wms_so_status = $status;
                $this->order->wms_so_failure_reason = $responseData['data']['failure_reason'] ?? null;
                $this->order->save();

                Log::info('WMS Sync: Sales Order successfully sent.', [
                    'order_number' => $this->order->order_number,
                    'status' => $status
                ]);
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $response->body();
                
                if (is_array($errorMessage)) {
                    $errorMessage = implode(', ', $errorMessage);
                }

                $this->order->wms_so_status = 'FAILED';
                $this->order->wms_so_failure_reason = substr($errorMessage, 0, 500);
                $this->order->save();

                Log::error('WMS Sync: Failed to send Sales Order.', [
                    'order_number' => $this->order->order_number,
                    'status_code' => $response->status(),
                    'error' => $errorMessage,
                    'payload' => $payload
                ]);
            }
        } catch (\Exception $e) {
            $this->order->wms_so_status = 'FAILED';
            $this->order->wms_so_synced_at = now();
            $this->order->wms_so_failure_reason = substr($e->getMessage(), 0, 500);
            $this->order->save();

            Log::error('WMS Sync: Exception when sending Sales Order.', [
                'order_number' => $this->order->order_number,
                'exception' => $e->getMessage()
            ]);
        }
    }
}

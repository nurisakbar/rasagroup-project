<?php

namespace App\Jobs;

use App\Exceptions\WmsBatchValidationException;
use App\Models\Order;
use App\Services\WmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSalesOrderToWmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    /** @var list<int> */
    public array $backoff = [30, 60, 120, 300, 600];

    public function __construct(public Order $order)
    {
    }

    public function handle(WmsService $wms): void
    {
        $order = $this->order->fresh(['items.product', 'user', 'sourceWarehouse']);
        if (! $order) {
            return;
        }

        $this->order = $order;
        $order->loadMissing('items.product');

        $apiUrl = config('services.wms.api_url');
        $apiKey = config('services.wms.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            Log::warning('WMS API is not configured. Skipping Sales Order Sync.', ['order_number' => $order->order_number]);

            return;
        }

        try {
            $this->assertOrderedItemBatches($order, $wms);
        } catch (WmsBatchValidationException $e) {
            $this->markFailed($order, $e->getMessage());

            return;
        } catch (\Throwable $e) {
            $this->markFailed($order, $e->getMessage());

            throw $e;
        }

        $itemsPayload = [];

        foreach ($order->items as $item) {
            $batchesPayload = [];
            $totalBatchQty = 0;

            if (! empty($item->allocated_batches) && is_array($item->allocated_batches)) {
                foreach ($item->allocated_batches as $batch) {
                    $batchesPayload[] = [
                        'batch_number' => $batch['lot_serial'] ?? '',
                        'location_code' => $order->source_qad_location_code ?? '',
                        'quantity' => (float) ($batch['qty'] ?? 0),
                    ];
                    $totalBatchQty += (float) ($batch['qty'] ?? 0);
                }
            }

            if ($totalBatchQty != $item->quantity) {
                Log::warning('WMS Sync: Item quantity does not match allocated batches.', [
                    'order_number' => $order->order_number,
                    'item_code' => $item->product->code,
                    'item_quantity' => $item->quantity,
                    'allocated_total' => $totalBatchQty,
                ]);
            }

            $itemsPayload[] = [
                'item_code' => $item->product->code,
                'quantity' => (float) $item->quantity,
                'batches' => $batchesPayload,
            ];
        }

        $payload = [
            'so_number' => $order->order_number,
            'items' => $itemsPayload,
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->post(rtrim($apiUrl, '/') . '/sales-orders', $payload);

            $order->wms_so_synced_at = now();

            if ($response->successful()) {
                $responseData = $response->json();
                $status = $responseData['data']['status'] ?? 'QUEUED';

                if ($response->status() === 200) {
                    $status = $responseData['data']['status'] ?? 'SUCCESS';
                }

                $order->wms_so_status = $status;
                $order->wms_so_failure_reason = $responseData['data']['failure_reason'] ?? null;
                $order->save();

                Log::info('WMS Sync: Sales Order successfully sent.', [
                    'order_number' => $order->order_number,
                    'status' => $status,
                ]);

                return;
            }

            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $response->body();
            if (is_array($errorMessage)) {
                $errorMessage = implode(', ', $errorMessage);
            }

            $this->markFailed($order, (string) $errorMessage);

            Log::error('WMS Sync: Failed to send Sales Order.', [
                'order_number' => $order->order_number,
                'status_code' => $response->status(),
                'error' => $errorMessage,
                'payload' => $payload,
            ]);

            if ($response->serverError()) {
                throw new \RuntimeException('WMS sales-orders HTTP '.$response->status().': '.$errorMessage);
            }
        } catch (WmsBatchValidationException $e) {
            $this->markFailed($order, $e->getMessage());
        } catch (ConnectionException $e) {
            $this->markFailed($order, $e->getMessage());
            throw $e;
        } catch (\RuntimeException $e) {
            if (str_starts_with($e->getMessage(), 'WMS sales-orders HTTP')) {
                throw $e;
            }

            $this->markFailed($order, $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->markFailed($order, $e->getMessage());

            Log::error('WMS Sync: Exception when sending Sales Order.', [
                'order_number' => $order->order_number,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function assertOrderedItemBatches(Order $order, WmsService $wms): void
    {
        $location = $order->source_qad_location_code ?: WmsService::locationCode($order->sourceWarehouse);
        if (! $location) {
            throw new WmsBatchValidationException('Hub belum punya kode lokasi WMS/QAD.');
        }

        $itemCodes = $order->items
            ->map(fn ($item) => $item->product?->code)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $grouped = $wms->batchesForItemCodes(
            $location,
            $itemCodes,
            $order->user?->shelfLifeMonths() ?? 0
        );

        foreach ($order->items as $item) {
            $productCode = (string) ($item->product?->code ?? '');
            $productName = $item->product?->name ?: $productCode;
            $allocated = is_array($item->allocated_batches) ? $item->allocated_batches : [];

            if ($allocated === []) {
                throw new WmsBatchValidationException("Batch belum dipilih untuk {$productName}.");
            }

            $available = $wms->batchesForProduct($grouped, $productCode);
            $used = [];

            foreach ($allocated as $batch) {
                $lot = trim((string) ($batch['lot_serial'] ?? ''));
                $qty = (int) ($batch['qty'] ?? 0);
                if ($lot === '' || $qty < 1) {
                    throw new WmsBatchValidationException("Batch tidak valid untuk {$productName}.");
                }

                $found = null;
                foreach ($available as $row) {
                    if ((string) $row['lot_serial'] === $lot) {
                        $found = $row;
                        break;
                    }
                }

                if (! $found) {
                    throw new WmsBatchValidationException("Batch {$lot} tidak tersedia di WMS untuk {$productName}.");
                }

                $used[$lot] = ($used[$lot] ?? 0) + $qty;
                if ($used[$lot] > (int) $found['qty']) {
                    throw new WmsBatchValidationException(
                        "Qty {$productName} batch {$lot} melebihi stok WMS (".(int) $found['qty'].').'
                    );
                }
            }
        }
    }

    private function markFailed(Order $order, string $message): void
    {
        $order->wms_so_status = 'FAILED';
        $order->wms_so_synced_at = now();
        $order->wms_so_failure_reason = substr($message, 0, 500);
        $order->save();
    }
}

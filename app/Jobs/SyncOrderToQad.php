<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\QadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderToQad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load([
            'user',
            'items.product',
            'address.province',
            'address.regency',
            'address.district',
            'sourceWarehouse',
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(QadService $qadService): void
    {
        $user = $this->order->user;
        $address = $this->order->address;

        if (!$user || !$address) {
            Log::error("SyncOrderToQad: Missing user or address for order {$this->order->id}");
            return;
        }

        Log::info('SyncOrderToQad: Start', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'has_qad_customer_code' => !empty($user->qad_customer_code),
            'has_qad_so_number' => !empty($this->order->qad_so_number),
        ]);

        // 1. Ensure Customer exists in QAD
        if (!$user->qad_customer_code) {
            // Delegate to dedicated job (can be retried independently)
            $addressSnapshot = [
                'city' => $address->regency?->name ?? 'Jakarta',
                'street1' => $address->address_detail ?? $address->full_address ?? '-',
                'street2' => trim((string) (($address->district?->name ?? '') . ' ' . ($address->regency?->name ?? ''))),
            ];
            \App\Jobs\SyncCustomerToQad::dispatchSync($user, $addressSnapshot);
            $user = $this->order->user->fresh();
        }

        // 2. Sync Sales Order
        if (!$this->order->qad_so_number) {
            $forceAttempt = (bool) config('qidapi.force_so', env('QIDAPI_FORCE_SO', true)); // Default to true if not set
            if (!$forceAttempt && !$qadService->canPosting()) {
                Log::error('SyncOrderToQad: QID token has can_posting=false; cannot create Sales Order. Update QIDAPI credentials to a user with posting permission.', [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'qid_username' => $qadService->getUserInfo()['username'] ?? null,
                    'force_attempt' => $forceAttempt,
                ]);
                return;
            }
            $this->syncSalesOrder($qadService, $user);
        }
    }

    protected function syncSalesOrder(QadService $qadService, $user)
    {
        $user->refresh(); // Get updated qad_customer_code
        if (!$user->qad_customer_code) {
            Log::error("SyncOrderToQad: Cannot sync SO because user has no QAD customer code", ['order_id' => $this->order->id]);
            return;
        }

        // Persisted in DB so each order keeps a stable WSxxxxxx number for QID sync attempts.
        $qidSalesOrderNumber = $this->getOrCreateQidSalesOrderNumber();
        $purchaseOrderNumber = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', (string) $this->order->order_number));
        $purchaseOrderNumber = substr($purchaseOrderNumber, -10);
        if ($purchaseOrderNumber === '') {
            $purchaseOrderNumber = $qidSalesOrderNumber;
        }

        // Use fixed QAD location to avoid invalid hub code mapping.
        $locationCode = 'FG001';
        $itemUomCache = [];

        $lines = [];
        $invalidPriceItems = [];
        foreach ($this->order->items as $index => $item) {
            $price = (float) ($item->price ?? 0);
            if ($price <= 0 && $item->relationLoaded('product') && $item->product) {
                $price = (float) ($item->product->price ?? 0);
            }
            if ($price <= 0) {
                $itemCode = $item->product->code ?? $item->product->name ?? null;
                $qadDefaultPrice = null;
                if ($itemCode) {
                    $itemRes = $qadService->getItem($itemCode);
                    $qadDefaultPrice = (float) ($itemRes['data']['defaultPrice'] ?? 0);
                }

                if ($qadDefaultPrice > 0) {
                    $price = $qadDefaultPrice;
                }
            }

            if ($price <= 0) {
                $invalidPriceItems[] = [
                    'order_item_id' => $item->id ?? null,
                    'item_code' => $item->product?->code ?? null,
                    'order_item_price' => (float) ($item->price ?? 0),
                    'product_master_price' => (float) ($item->product?->price ?? 0),
                ];
                Log::error('SyncOrderToQad: Invalid price for SO line', [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'order_item_id' => $item->id ?? null,
                    'item_code' => $item->product?->code ?? null,
                    'original_price' => $price,
                    'product_master_price' => (float) ($item->product?->price ?? 0),
                ]);
            }

            $itemCode = $item->product->code ?? $item->product->name;
            $uom = $item->product->unit ?? null;
            if (!$uom && $itemCode) {
                if (!array_key_exists($itemCode, $itemUomCache)) {
                    $itemRes = $qadService->getItem($itemCode);
                    $itemUomCache[$itemCode] = $itemRes['data']['uom'] ?? null;
                }
                $uom = $itemUomCache[$itemCode];
            }

            $lines[] = [
                "salesOrderNumber" => $qidSalesOrderNumber,
                "salesOrderLine" => $index + 1,
                "itemCode" => $itemCode,
                "quantityOrdered" => (int) $item->quantity,
                "listPrice" => $price,
                "netPrice" => $price,
                "discountPercent" => 0,
                "dueDate" => $this->order->created_at->addDays(7)->format('Y-m-d') . "T00:00:00.000Z",
                "isTaxable" => true,
                "salesAcct" => "41101",
                "salesCC" => "",
                "discountAcct" => "41101",
                "discountCC" => "",
                "unitOfMeasure" => $uom ?: "BT",
                "siteCode" => "MCR",
                "locationCode" => $locationCode,
            ];
        }

        if (!empty($invalidPriceItems)) {
            Log::error('SyncOrderToQad: Abort create SO because one or more item prices are invalid (<= 0). Please fix product/order master pricing first.', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'invalid_items' => $invalidPriceItems,
            ]);
            return;
        }

        $payload = [
            "domainCode" => "MCR",
            "salesOrderNumber" => $qidSalesOrderNumber,
            "billToCustomerCode" => $user->qad_customer_code,
            "soldToCustomerCode" => $user->qad_customer_code,
            "shipToCustomerCode" => $user->qad_customer_code,
            "orderDate" => $this->order->created_at->format('Y-m-d') . "T00:00:00.000Z",
            "dueDate" => $this->order->created_at->addDays(7)->format('Y-m-d') . "T00:00:00.000Z",
            "requiredDate" => $this->order->created_at->addDays(7)->format('Y-m-d') . "T00:00:00.000Z",
            "shipDate" => $this->order->created_at->addDays(7)->format('Y-m-d') . "T00:00:00.000Z",
            "promiseDate" => $this->order->created_at->addDays(7)->format('Y-m-d') . "T00:00:00.000Z",
            "creditTermsCode" => "CIA",
            "remarks" => $this->order->notes ?? ("Order from Website: " . $this->order->order_number),
            "purchaseOrderNumber" => $purchaseOrderNumber,
            "taxClass" => "PPN",
            "taxEnvironment" => "IDN",
            "isTaxable" => true,
            "isConfirmed" => true,
            "salespersonCode_01" => "SLS00001",
            "isSelfBillingEnabled" => true,
            "currencyCode" => "IDR",
            "siteCode" => "MCR",
            "salesOrderLines" => $lines
        ];

        Log::info("SyncOrderToQad: Checking if Sales Order already exists in QAD", [
            'qid_so_number' => $qidSalesOrderNumber,
        ]);
        $checkRes = $qadService->getSalesOrder($qidSalesOrderNumber);
        $existingSo = $checkRes['data'] ?? null;

        if ($existingSo) {
            $soNumber = $existingSo['salesOrderNumber'] ?? $existingSo['salesOrderCode'] ?? null;
            if ($soNumber) {
                $this->order->update(['qad_so_number' => $soNumber]);
                Log::info("SyncOrderToQad: Sales Order already exists in QAD, linked existing", ['qad_so' => $soNumber]);
                return;
            }
        }

        Log::info("SyncOrderToQad: Creating Sales Order in QAD", [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_code' => $user->qad_customer_code,
            'payload' => $payload,
        ]);
        $result = $qadService->createSalesOrder($payload);

        // Postman collection shows `data` can be an object OR array (list) depending on endpoint/version.
        $data = $result['data'] ?? null;
        if (is_array($data) && array_is_list($data)) {
            $data = $data[0] ?? null;
        }

        $soNumber =
            (is_array($data) ? ($data['salesOrderNumber'] ?? $data['salesOrderCode'] ?? null) : null) ??
            $result['salesOrderNumber'] ??
            $result['salesOrderCode'] ??
            null;

        Log::info('SyncOrderToQad: Create SO response', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'has_result' => !empty($result),
            'result_keys' => is_array($result) ? array_keys($result) : null,
            'data_type' => is_array($result) ? gettype($result['data'] ?? null) : null,
            'extracted_so_number' => $soNumber,
            'error_is_error' => $result['error']['isError'] ?? null,
            'error_messages' => $result['error']['errorMessages'] ?? null,
            'message' => $result['message'] ?? null,
        ]);

        if ($result && $soNumber) {
            $this->order->update(['qad_so_number' => $soNumber]);
            Log::info("SyncOrderToQad: Sales Order created in QAD", ['qad_so' => $soNumber]);
        } else {
            // Handle collision by incrementing local sequence if it was a BadRequest on SO creation
            $errorMsg = json_encode($result['error'] ?? $result);
            if (str_contains(strtolower($errorMsg), 'badrequest') || str_contains(strtolower($errorMsg), 'exists')) {
                Log::warning("SyncOrderToQad: Likely SO number collision or bad request, clearing local qid_sales_order_number to retry on next run", [
                    'order_id' => $this->order->id,
                    'error' => $errorMsg
                ]);
                $this->order->update(['qid_sales_order_number' => null]);
            }
            Log::error("SyncOrderToQad: Failed to create sales order in QAD", ['order_id' => $this->order->id, 'response' => $result]);
        }
    }

    protected function getOrCreateQidSalesOrderNumber(): string
    {
        $existing = (string) ($this->order->qid_sales_order_number ?? '');
        if ($existing !== '') {
            return $existing;
        }

        $nextNumber = DB::transaction(function () {
            $last = Order::query()
                ->whereNotNull('qid_sales_order_number')
                ->where('qid_sales_order_number', 'like', 'WS%')
                ->orderByDesc('qid_sales_order_number')
                ->lockForUpdate()
                ->value('qid_sales_order_number');

            $lastSequence = (int) preg_replace('/\D/', '', (string) $last);
            $nextSequence = $lastSequence + 1;
            if ($nextSequence < 100001 && $last === null) {
                $nextSequence = 100001;
            }
            if ($nextSequence > 999999) {
                $nextSequence = 1;
            }

            return 'WS' . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });

        $this->order->update(['qid_sales_order_number' => $nextNumber]);
        $this->order->refresh();

        return $nextNumber;
    }
}

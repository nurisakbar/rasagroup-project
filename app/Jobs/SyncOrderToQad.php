<?php

namespace App\Jobs;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\QadService;
use App\Support\QadBusinessRelationHeadOffice;
use App\Support\QadWsOrderNumberGenerator;
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
        // Pakai onConnection (bukan property) agar tidak bentrok dengan trait Queueable.
        $this->onConnection('sync');

        $this->order = $order->load([
            'user',
            'items.product',
            'address.province',
            'address.regency',
            'address.district.city',
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

        // 1. Ensure Customer exists in QAD (and the code is valid)
        $user = $this->ensureQadCustomerReady($qadService, $user, $address, false);
        if (!$user || !$user->qad_customer_code) {
            Log::error('SyncOrderToQad: Cannot sync because user has no valid QAD customer code after customer sync', [
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
            ]);
            return;
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

    protected function syncSalesOrder(QadService $qadService, User $user): void
    {
        $user->refresh(); // Get updated qad_customer_code
        if (!$user->qad_customer_code) {
            Log::error("SyncOrderToQad: Cannot sync SO because user has no QAD customer code", ['order_id' => $this->order->id]);
            return;
        }

        $orderAddress = $this->order->address;
        if ($orderAddress) {
            QadBusinessRelationHeadOffice::patch(
                $qadService,
                $user,
                $user->qad_customer_code,
                $this->buildOrderAddressSnapshot($orderAddress)
            );
        }

        $dataRes = $qadService->createCustomerData(['customerCode' => $user->qad_customer_code]);
        if (is_array($dataRes) && ($dataRes['error']['isError'] ?? false)) {
            Log::warning('SyncOrderToQad: createCustomerData returned error; continuing SO create', [
                'order_id' => $this->order->id,
                'customer_code' => $user->qad_customer_code,
                'response' => $dataRes,
            ]);
        }

        // Persisted in DB so each order keeps a stable WSxxxxxx number for QID sync attempts.
        $qidSalesOrderNumber = $this->getOrCreateQidSalesOrderNumber();

        $lineDueDate = $this->order->created_at->copy()->startOfDay()->addDays(7)->format('Y-m-d') . 'T00:00:00.000Z';

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

            // Sama struktur & tipe dengan payload contoh yang valid (harga integer IDR, tanpa site/location di baris).
            $linePrice = (int) round(max(0.0, (float) $price));
            $lines[] = [
                'salesOrderNumber' => $qidSalesOrderNumber,
                'salesOrderLine' => $index + 1,
                'itemCode' => $itemCode,
                'quantityOrdered' => (int) $item->quantity,
                'unitOfMeasure' => $uom ?: 'PK',
                'listPrice' => $linePrice,
                'discountPercent' => 0,
                'netPrice' => $linePrice,
                'dueDate' => $lineDueDate,
                'isTaxable' => true,
                'salesAcct' => '41101',
                'salesCC' => '',
                'discountAcct' => '41101',
                'discountCC' => '',
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

        $orderDate = $this->order->created_at->copy()->startOfDay();

        Log::info("SyncOrderToQad: Checking if Sales Order already exists in QAD", [
            'qid_so_number' => $qidSalesOrderNumber,
        ]);
        $checkRes = $qadService->getSalesOrder($qidSalesOrderNumber);
        $existingSo = (is_array($checkRes) && ! ($checkRes['error']['isError'] ?? false))
            ? ($checkRes['data'] ?? null)
            : null;

        if ($existingSo) {
            $soNumber = $existingSo['salesOrderNumber'] ?? $existingSo['salesOrderCode'] ?? null;
            if ($soNumber) {
                $this->order->update(['qad_so_number' => $soNumber]);
                Log::info("SyncOrderToQad: Sales Order already exists in QAD, linked existing", ['qad_so' => $soNumber]);
                return;
            }
        }

        $result = null;
        $soNumber = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            if ($attempt > 1) {
                Log::warning('SyncOrderToQad: Retrying Sales Order create with new WS + PO', [
                    'order_id' => $this->order->id,
                    'attempt' => $attempt,
                ]);
                $this->order->update(['qid_sales_order_number' => null]);
                $this->order->refresh();
                $qidSalesOrderNumber = $this->getOrCreateQidSalesOrderNumber();
                foreach ($lines as $ix => $line) {
                    $lines[$ix]['salesOrderNumber'] = $qidSalesOrderNumber;
                }
            }

            $purchaseOrderNumber = $this->buildPurchaseOrderNumberForQad($attempt);

            // Tanggal header sama semua seperti payload uji yang berhasil (qid:test-so-format); due baris tetap +7 hari.
            $headerDateIso = $orderDate->format('Y-m-d') . 'T00:00:00.000Z';

            // Selaras swagger SalesOrder_Create + contoh payload internal (tanpa field ekstra di header/baris).
            $payload = [
                'domainCode' => 'MCR',
                'salesOrderNumber' => $qidSalesOrderNumber,
                'billToCustomerCode' => $user->qad_customer_code,
                'soldToCustomerCode' => $user->qad_customer_code,
                'shipToCustomerCode' => $user->qad_customer_code,
                'orderDate' => $headerDateIso,
                'dueDate' => $headerDateIso,
                'requiredDate' => $headerDateIso,
                'shipDate' => $headerDateIso,
                'promiseDate' => $headerDateIso,
                'creditTermsCode' => 'CIA',
                'remarks' => ($this->order->notes !== null && trim((string) $this->order->notes) !== '')
                    ? (string) $this->order->notes
                    : (string) $this->order->order_number,
                'purchaseOrderNumber' => $purchaseOrderNumber,
                'taxClass' => 'PPN',
                'isTaxable' => true,
                'salespersonCode_01' => 'SLS00001',
                'isSelfBillingEnabled' => true,
                'salesOrderLines' => $lines,
            ];

            Log::info('SyncOrderToQad: Creating Sales Order in QAD', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'customer_code' => $user->qad_customer_code,
                'attempt' => $attempt,
                'payload' => $payload,
            ]);

            $result = $qadService->createSalesOrder($payload);
            $soNumber = $this->extractSalesOrderNumberFromQidResponse($result);

            Log::info('SyncOrderToQad: Create SO response', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'attempt' => $attempt,
                'has_result' => is_array($result) && $result !== [],
                'result_keys' => is_array($result) ? array_keys($result) : null,
                'extracted_so_number' => $soNumber,
                'error_is_error' => is_array($result) ? ($result['error']['isError'] ?? null) : null,
                'error_messages' => is_array($result) ? ($result['error']['errorMessages'] ?? null) : null,
                'message' => is_array($result) ? ($result['message'] ?? null) : null,
            ]);

            if ($soNumber) {
                $this->order->update(['qad_so_number' => $soNumber]);
                Log::info('SyncOrderToQad: Sales Order created in QAD', ['qad_so' => $soNumber]);

                return;
            }

            $errorMsg = json_encode(is_array($result) ? ($result['error'] ?? $result) : []);
            $retryable = str_contains(strtolower($errorMsg), 'badrequest')
                || str_contains(strtolower($errorMsg), 'exists')
                || str_contains(strtolower($errorMsg), 'duplicate');

            if ($retryable) {
                // BadRequest sering terjadi jika master customer belum valid di sisi QAD.
                // Flow: re-issue customer terlebih dulu, baru create SO ulang.
                $orderAddress = $this->order->address;
                if ($orderAddress) {
                    $refreshedUser = $this->ensureQadCustomerReady($qadService, $user, $orderAddress, true);
                    if ($refreshedUser && $refreshedUser->qad_customer_code) {
                        $user = $refreshedUser;
                    } else {
                        Log::error('SyncOrderToQad: Retryable SO error but could not re-issue valid customer code', [
                            'order_id' => $this->order->id,
                            'user_id' => $this->order->user_id,
                        ]);
                        break;
                    }
                }

                $this->order->update(['qid_sales_order_number' => null]);
                $this->order->refresh();
            }

            if ($attempt === 2 || ! $retryable) {
                break;
            }
        }

        Log::error('SyncOrderToQad: Failed to create sales order in QAD', [
            'order_id' => $this->order->id,
            'response' => $result,
        ]);
    }

    /**
     * Nomor PO 10 digit numerik seperti contoh sukses (2604280002): yymmdd + 4 digit unik per order/percobaan.
     */
    protected function buildPurchaseOrderNumberForQad(int $attempt): string
    {
        $ymd = $this->order->created_at->format('ymd');
        $n = (abs(crc32((string) $this->order->id)) + $attempt * 9973) % 10000;

        return $ymd . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    protected function extractSalesOrderNumberFromQidResponse(?array $result): ?string
    {
        if (! is_array($result) || ($result['error']['isError'] ?? false)) {
            return null;
        }

        $data = $result['data'] ?? null;
        if (is_array($data) && array_is_list($data)) {
            $data = $data[0] ?? null;
        }

        $soNumber = (is_array($data) ? ($data['salesOrderNumber'] ?? $data['salesOrderCode'] ?? null) : null)
            ?? ($result['salesOrderNumber'] ?? null)
            ?? ($result['salesOrderCode'] ?? null);

        return is_string($soNumber) && $soNumber !== '' ? $soNumber : null;
    }

    protected function getOrCreateQidSalesOrderNumber(): string
    {
        $existing = (string) ($this->order->qid_sales_order_number ?? '');
        if ($existing !== '') {
            return $existing;
        }

        $orderNumber = (string) ($this->order->order_number ?? '');
        if (preg_match('/^WS\d{6}$/', $orderNumber)) {
            $this->order->update(['qid_sales_order_number' => $orderNumber]);
            $this->order->refresh();

            return $orderNumber;
        }

        $nextNumber = QadWsOrderNumberGenerator::generate();

        $this->order->update(['qid_sales_order_number' => $nextNumber]);
        $this->order->refresh();

        return $nextNumber;
    }

    protected function ensureQadCustomerReady(QadService $qadService, User $user, Address $address, bool $forceReissue): ?User
    {
        $user = $user->fresh();
        if (! $user) {
            return null;
        }

        if ($forceReissue) {
            Log::warning('SyncOrderToQad: Forcing QAD customer re-issue before SO retry', [
                'order_id' => $this->order->id,
                'user_id' => $user->id,
                'previous_qad_customer_code' => $user->qad_customer_code,
            ]);
            $user->update(['qad_customer_code' => null]);
            $user->refresh();
        }

        if ($user->qad_customer_code) {
            $check = $qadService->getCustomer((string) $user->qad_customer_code, 'MCR-CUST');
            $valid = is_array($check)
                && ! ($check['error']['isError'] ?? false)
                && is_array($check['data'] ?? null)
                && ! empty($check['data']['customerCode'] ?? null);

            if (! $valid) {
                Log::warning('SyncOrderToQad: Existing qad_customer_code is not valid in QAD, clearing and re-syncing', [
                    'order_id' => $this->order->id,
                    'user_id' => $user->id,
                    'qad_customer_code' => $user->qad_customer_code,
                    'check' => $check,
                ]);
                $user->update(['qad_customer_code' => null]);
                $user->refresh();
            }
        }

        if (! $user->qad_customer_code) {
            $addressSnapshot = $this->buildOrderAddressSnapshot($address);
            \App\Jobs\SyncCustomerToQad::dispatchSync($user, $addressSnapshot);
            $user = $user->fresh();
        }

        // If we still can't obtain a valid customer code, optionally fall back to an approved master.
        if (! $user->qad_customer_code) {
            $fallback = trim((string) config('qidapi.fallback_customer_code', ''));
            if ($fallback !== '') {
                $check = $qadService->getCustomer($fallback, 'MCR-CUST');
                $valid = is_array($check)
                    && ! ($check['error']['isError'] ?? false)
                    && is_array($check['data'] ?? null)
                    && ! empty($check['data']['customerCode'] ?? null);

                if ($valid) {
                    Log::warning('SyncOrderToQad: Using fallback QAD customer code because user customer sync failed', [
                        'order_id' => $this->order->id,
                        'user_id' => $user->id,
                        'fallback_customer_code' => $fallback,
                    ]);
                    $user->update(['qad_customer_code' => $fallback]);
                    $user->refresh();
                } else {
                    Log::warning('SyncOrderToQad: Fallback customer code is not valid in QAD', [
                        'order_id' => $this->order->id,
                        'user_id' => $user->id,
                        'fallback_customer_code' => $fallback,
                        'check' => $check,
                    ]);
                }
            }
        }

        return $user;
    }

    /**
     * @return array{city: string, street1: string, street2: string, postal_code: string}
     */
    protected function buildOrderAddressSnapshot(Address $address): array
    {
        $postal = '';
        foreach ([
            $address->postal_code,
            $address->district?->postal_code,
            $address->regency?->postal_code,
            $address->district?->city?->postal_code,
        ] as $p) {
            $p = trim((string) $p);
            if ($p !== '' && preg_match('/^\d{5}$/', $p)) {
                $postal = $p;
                break;
            }
        }
        if ($postal === '' && $address->address_detail && preg_match('/\b(\d{5})\b/', (string) $address->address_detail, $m)) {
            $postal = $m[1];
        }

        return [
            'city' => $address->regency?->name ?? 'Jakarta',
            'street1' => $address->address_detail ?? $address->full_address ?? '-',
            'street2' => trim((string) (($address->district?->name ?? '') . ' ' . ($address->regency?->name ?? ''))),
            'postal_code' => $postal,
        ];
    }
}

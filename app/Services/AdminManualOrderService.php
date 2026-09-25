<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\WmsService;
use App\Support\QadWsOrderNumberGenerator;
use App\Support\SalesOrderSyncDispatcher;
use App\Support\ShopFulfillment;
use App\Support\TaxAwarePrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminManualOrderService
{
    /**
     * @param  array<int, array{product: Product, quantity: int, order_uom: string, quantity_ordered: int}>  $normalizedItems
     */
    public function preview(User $user, array $normalizedItems, float $shippingCost, ?bool $pakaiPpn = null): array
    {
        $user->loadMissing(['priceLevel', 'categoryDiscounts']);
        $pakaiPpn ??= $user->usesPpn();
        $taxPercent = TaxAwarePrice::percentIfEnabled($pakaiPpn);

        $lines = [];
        $soldSubtotal = 0.0;
        $catalogSubtotal = 0.0;
        $dppSubtotal = 0.0;
        $ppnTotal = 0.0;
        $inclusiveSubtotal = 0.0;

        foreach ($normalizedItems as $item) {
            /** @var Product $product */
            $product = $item['product'];
            $qty = (int) $item['quantity'];
            $unit = $this->unitPrice($user, $product, $taxPercent);
            $lineSubtotal = $unit * $qty;
            $catalog = (float) $product->price * $qty;
            $tax = TaxAwarePrice::breakdown($lineSubtotal, $catalog, $taxPercent);

            $catalogInclusive = (float) $product->price;
            $catalogDpp = TaxAwarePrice::excludingTax($catalogInclusive, $taxPercent);
            $discountPercent = $user->categoryDiscountPercentageFor($product);

            $lines[] = [
                'product_id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'catalog_price' => $catalogInclusive,
                'catalog_dpp' => round($catalogDpp, 2),
                'discount_percent' => $discountPercent,
                'unit_price' => $unit,
                'quantity' => $qty,
                'order_uom' => $item['order_uom'],
                'quantity_ordered' => $item['quantity_ordered'],
                'lot_serial' => $item['lot_serial'] ?? null,
                'allocated_batches' => $item['allocated_batches'] ?? [],
                'subtotal' => $lineSubtotal,
                'dpp' => $tax['dpp'],
                'ppn' => $tax['ppn'],
            ];

            $soldSubtotal += $lineSubtotal;
            $catalogSubtotal += $catalog;
            $dppSubtotal += $tax['dpp'];
            $ppnTotal += $tax['ppn'];
            $inclusiveSubtotal += $tax['inclusive'];
        }

        $shippingCost = max(0, $shippingCost);

        return [
            'lines' => $lines,
            'catalog_subtotal' => round($catalogSubtotal, 2),
            'distributor_discount' => round(max(0, $catalogSubtotal - $soldSubtotal), 2),
            'subtotal_dpp' => round($dppSubtotal, 2),
            'subtotal' => round($inclusiveSubtotal, 2),
            'ppn' => round($ppnTotal, 2),
            'ppn_label' => TaxAwarePrice::ppnLabel($taxPercent),
            'tax_percent' => $taxPercent,
            'pakai_ppn' => $pakaiPpn,
            'shipping_cost' => round($shippingCost, 2),
            'total' => round($inclusiveSubtotal + $shippingCost, 2),
            'order_type' => $user->isDistributor() ? Order::TYPE_DISTRIBUTOR : Order::TYPE_REGULAR,
            'price_level' => $user->priceLevel?->name,
        ];
    }

    /**
     * @param  array<int, array{product_id: string, quantity_ordered: int, order_uom: string}>  $rawItems
     * @return array<int, array{product: Product, quantity: int, order_uom: string, quantity_ordered: int}>
     */
    public function normalizeItems(array $rawItems): array
    {
        $normalized = [];
        $seen = [];

        foreach ($rawItems as $row) {
            $product = Product::query()->where('status', 'active')->find($row['product_id'] ?? null);
            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'Produk tidak ditemukan atau tidak aktif.',
                ]);
            }

            $uom = ($row['order_uom'] ?? 'base') === 'large' ? 'large' : 'base';
            $ordered = (int) ($row['quantity_ordered'] ?? 0);
            $baseQty = $product->orderedQuantityToBase($ordered, $uom);
            $lot = trim((string) ($row['lot_serial'] ?? ''));
            $mergeKey = $product->id;

            if ($baseQty < 1) {
                throw ValidationException::withMessages([
                    'items' => "Qty tidak valid untuk {$product->name}.",
                ]);
            }

            if (isset($seen[$mergeKey])) {
                $idx = $seen[$mergeKey];
                $normalized[$idx]['quantity'] += $baseQty;
                $normalized[$idx]['quantity_ordered'] += $ordered;
                continue;
            }

            $seen[$mergeKey] = count($normalized);
            $normalized[] = [
                'product' => $product,
                'quantity' => $baseQty,
                'order_uom' => $uom === 'large' && $product->hasDualUnitOrdering()
                    ? ($product->large_unit ?? 'CTN')
                    : ($product->unit ?: 'base'),
                'quantity_ordered' => $ordered,
                'lot_serial' => $lot,
            ];
        }

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu produk harus dipilih.',
            ]);
        }

        return $normalized;
    }

    /**
     * @param  array{
     *     expedition_service: string,
     *     shipping_cost: float,
     *     payment_method: string,
     *     mark_as_paid?: bool,
     *     notes?: ?string,
     *     sales_code?: ?string,
     *     preferred_shipping_date?: ?string,
     *     admin_name?: ?string,
     *     pakai_ppn?: bool,
     *     purchase_order_number?: ?string,
     *     purchase_order_document?: ?string
     * }  $payload
     */
    public function create(
        User $customer,
        Warehouse $warehouse,
        Address $address,
        Expedition $expedition,
        array $normalizedItems,
        array $payload
    ): Order {
        $customer->loadMissing(['priceLevel', 'categoryDiscounts']);

        $paymentMethod = $payload['payment_method'];
        $this->assertPaymentAllowed($customer, $paymentMethod);

        $pakaiPpn = array_key_exists('pakai_ppn', $payload)
            ? (bool) $payload['pakai_ppn']
            : $customer->usesPpn();
        $pricing = $this->preview($customer, $normalizedItems, (float) $payload['shipping_cost'], $pakaiPpn);
        $subtotal = $pricing['subtotal'];
        $shippingCost = $pricing['shipping_cost'];
        $total = $pricing['total'];
        $orderType = $pricing['order_type'];

        $markAsPaid = (bool) ($payload['mark_as_paid'] ?? false);
        if ($paymentMethod === 'cash') {
            $markAsPaid = true;
        }
        if ($paymentMethod === 'term_of_payment') {
            $markAsPaid = false;
        }

        $pointsEarned = 0;
        if ($customer->isDriippreneurApproved()) {
            foreach ($normalizedItems as $item) {
                $pointsEarned += ((int) ($item['product']->reseller_point ?? 0)) * $item['quantity'];
            }
        }

        $shippingAddressText = $address->recipient_name . "\n" .
            $address->phone . "\n" .
            $address->address_detail . "\n" .
            ($address->district_name ? 'Kec. ' . $address->district_name . ', ' : '') .
            ($address->regency_name ? $address->regency_name . ', ' : '') .
            ($address->province_name ?? '') .
            ($address->postal_code ? ' ' . $address->postal_code : '');

        $adminName = $payload['admin_name'] ?? 'admin';
        $orderNotes = trim((string) ($payload['notes'] ?? ''));
        $prefix = 'Input admin (' . $adminName . '): penjualan manual.';
        if ($paymentMethod === 'term_of_payment' && $customer->term_of_payment) {
            $prefix .= ' TOT: pembayaran tempo ' . (int) $customer->term_of_payment . ' hari.';
        }
        $orderNotes = trim($prefix . (filled($orderNotes) ? ' | ' . $orderNotes : ''));

        $this->assertAndAllocateBatches($warehouse, $customer, $normalizedItems);

        $order = DB::transaction(function () use (
            $customer,
            $warehouse,
            $address,
            $expedition,
            $normalizedItems,
            $payload,
            $paymentMethod,
            $markAsPaid,
            $subtotal,
            $shippingCost,
            $total,
            $orderType,
            $pointsEarned,
            $shippingAddressText,
            $orderNotes,
            $pakaiPpn,
            $pricing
        ) {
            $orderNumber = QadWsOrderNumberGenerator::generate();
            $wmsLocationCode = WmsService::locationCode($warehouse) ?? ($warehouse->qad_location_code ?? $warehouse->kode_hub);

            $paymentStatus = 'pending';
            $orderStatus = 'pending';
            $paidAt = null;
            if ($markAsPaid) {
                $paymentStatus = 'paid';
                $orderStatus = 'processing';
                $paidAt = now();
            }

            $order = Order::create([
                'order_type' => $orderType,
                'order_number' => $orderNumber,
                'qid_sales_order_number' => $orderNumber,
                'user_id' => $customer->id,
                'address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'expedition_service' => $payload['expedition_service'],
                'source_warehouse_id' => $warehouse->id,
                'source_qad_location_code' => $wmsLocationCode,
                'subtotal' => $subtotal,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'shipping_cost' => $shippingCost,
                'pakai_ppn' => $pakaiPpn,
                'total_amount' => $total,
                'shipping_address' => $shippingAddressText,
                'payment_method' => $paymentMethod,
                'company' => $customer->getFaspayCompany(),
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'paid_at' => $paidAt,
                'notes' => $orderNotes,
                'points_earned' => $pointsEarned,
                'points_credited' => false,
                'sales_code' => $payload['sales_code'] ?? null,
                'preferred_shipping_date' => $payload['preferred_shipping_date'] ?? null,
                'purchase_order_number' => $payload['purchase_order_number'] ?? null,
                'purchase_order_document' => $payload['purchase_order_document'] ?? null,
            ]);

            foreach ($normalizedItems as $item) {
                $product = $item['product'];
                $lineUnit = $this->unitPrice($customer, $product, $pricing['tax_percent']);
                $allocatedBatches = $item['allocated_batches'] ?? [];
                if ($allocatedBatches === [] && filled($item['lot_serial'] ?? null)) {
                    $allocatedBatches = [[
                        'lot_serial' => $item['lot_serial'],
                        'qty' => $item['quantity'],
                        'expired' => $item['expired'] ?? null,
                    ]];
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'order_uom' => $item['order_uom'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'price' => $lineUnit,
                    'subtotal' => $lineUnit * $item['quantity'],
                    'allocated_batches' => $allocatedBatches ?: null,
                ]);

                if (! ShopFulfillment::assumeStockReady()) {
                    $stock = WarehouseStock::where('warehouse_id', $warehouse->id)
                        ->where('product_id', $product->id)
                        ->first();
                    if ($stock) {
                        $stock->decrement('stock', $item['quantity']);
                    }
                }
            }

            return $order;
        });

        $order->refresh();
        $order->load(['user', 'address', 'items.product', 'expedition', 'sourceWarehouse']);

        $this->afterCommit($order, $markAsPaid);

        return $order;
    }

    private function afterCommit(Order $order, bool $markAsPaid): void
    {
        try {
            $releaseToHub = ($order->payment_method === 'term_of_payment' && $order->finance_approved)
                || ($markAsPaid && $order->payment_status === 'paid');

            if (! $releaseToHub) {
                return;
            }

            if ($order->payment_status === 'paid') {
                $order->creditPoints();
                \App\Jobs\SendWhatsAppNotification::dispatch($order, 'thank_you');
            }

            \App\Jobs\SendWhatsAppNotification::dispatch($order, 'warehouse_notification');

            $order->loadMissing('sourceWarehouse.users');
            if ($order->sourceWarehouse) {
                $staffMembers = $order->sourceWarehouse->users;
                if ($staffMembers && $staffMembers->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send(
                        $staffMembers,
                        new \App\Notifications\Orders\NewTopOrderNotification($order)
                    );
                }
            }

            SalesOrderSyncDispatcher::dispatch($order);
            \App\Jobs\SendSalesOrderToWmsJob::dispatch($order);
        } catch (\Throwable $e) {
            Log::warning('Admin manual order: post-commit sync/notification failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function assertPaymentAllowed(User $customer, string $paymentMethod): void
    {
        $allowed = ['cash', 'manual_transfer'];
        if ($customer->isDistributor() && (int) ($customer->term_of_payment ?? 0) > 0) {
            $allowed[] = 'term_of_payment';
        }

        if (! in_array($paymentMethod, $allowed, true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak valid untuk pelanggan ini.',
            ]);
        }
    }

    /**
     * @param  array<int, array{product: Product, quantity: int, order_uom: string, quantity_ordered: int, lot_serial?: string, expired?: mixed}>  $normalizedItems
     */
    private function assertAndAllocateBatches(Warehouse $warehouse, User $customer, array &$normalizedItems): void
    {
        $location = WmsService::locationCode($warehouse);
        if (! $location) {
            throw ValidationException::withMessages([
                'source_warehouse_id' => 'Hub pengirim belum punya kode lokasi WMS/QAD.',
            ]);
        }

        $wms = app(WmsService::class);
        $pools = [];

        foreach ($normalizedItems as $i => $item) {
            $product = $item['product'];
            $productId = $product->id;
            if (! isset($pools[$productId])) {
                $pools[$productId] = $wms->batchesForWarehouseItem(
                    $warehouse,
                    (string) $product->code,
                    $customer->shelfLifeMonths()
                );
            }

            $taken = $wms->takeFromBatchPool($pools[$productId], (int) $item['quantity']);
            if ($taken['shortfall'] > 0 || $taken['allocated'] === []) {
                $available = 0;
                foreach ($pools[$productId] as $batch) {
                    $available += (int) ($batch['qty'] ?? 0);
                }
                foreach ($taken['allocated'] as $batch) {
                    $available += (int) ($batch['qty'] ?? 0);
                }
                throw ValidationException::withMessages([
                    'items' => "Stok batch {$product->name} tidak cukup. Butuh {$item['quantity']}, tersedia {$available}.",
                ]);
            }

            $normalizedItems[$i]['allocated_batches'] = $taken['allocated'];
            $normalizedItems[$i]['lot_serial'] = $taken['allocated'][0]['lot_serial'] ?? '';
            $normalizedItems[$i]['expired'] = $taken['allocated'][0]['expired'] ?? null;
        }
    }

    private function unitPrice(User $user, Product $product, float $taxPercent): float
    {
        return $user->getProductPrice($product);
    }
}

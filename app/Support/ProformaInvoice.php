<?php

namespace App\Support;

use App\Models\Address;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Warehouse;

class ProformaInvoice
{
    /**
     * @return list<string>
     */
    public static function relations(): array
    {
        return [
            'items.product',
            'user.categoryDiscounts',
            'address.wilayah',
            'address.province',
            'address.regency',
            'address.district',
            'sourceWarehouse.wilayah',
            'sourceWarehouse.province',
            'sourceWarehouse.regency',
            'sourceWarehouse.district',
        ];
    }

    /**
     * @return array{
     *     invoice_no: string,
     *     order_date: string,
     *     bill_name: string,
     *     from_lines: list<string>,
     *     bill_lines: list<string>,
     *     items: list<array{no: int, name: string, quantity: string, unit_price_before: float, unit_price_after: float, unit_price: float, total_price: float, discount_percent: float, has_discount: bool}>,
     *     subtotal_before: float,
     *     item_discount: float,
     *     subtotal: float,
     *     ppn: float,
     *     ppn_label: string,
     *     discount: float,
     *     discount_label: ?string,
     *     shipping: float,
     *     service_fee: float,
     *     total: float
     * }
     */
    public static function fromOrder(Order $order): array
    {
        $order->loadMissing(self::relations());

        $taxPercent = Setting::taxPercent();
        $dppBeforeTotal = 0.0;
        $dppAfterTotal = 0.0;
        $items = [];

        foreach ($order->items as $index => $item) {
            $qty = max(1, $item->displayQuantity());
            $priceBefore = $item->unitPriceBeforeDiscount();
            $priceAfter = $item->unitPriceAfterDiscount();
            $lineBefore = $priceBefore * $qty;
            $lineAfter = $priceAfter * $qty;
            $dppBeforeTotal += $lineBefore;
            $dppAfterTotal += $lineAfter;

            $items[] = [
                'no' => $index + 1,
                'name' => strtoupper((string) ($item->product?->display_name ?: $item->product?->name ?: 'Produk')),
                'quantity' => $item->orderedQuantityDescription(),
                'unit_price_before' => $priceBefore,
                'unit_price_after' => $priceAfter,
                'unit_price' => $priceAfter,
                'total_price' => $lineAfter,
                'discount_percent' => $item->unitDiscountPercent(),
                'has_discount' => ($priceBefore - $priceAfter) > 0.5,
            ];
        }

        $ppn = $taxPercent > 0 ? round($dppAfterTotal * ($taxPercent / 100), 2) : 0.0;

        return [
            'invoice_no' => self::invoiceNumber($order),
            'order_date' => $order->created_at?->format('m-d-Y') ?: now()->format('m-d-Y'),
            'bill_name' => self::billName($order),
            'from_lines' => self::fromLines($order->sourceWarehouse),
            'bill_lines' => self::billLines($order),
            'items' => $items,
            'subtotal_before' => round($dppBeforeTotal, 2),
            'item_discount' => round(max(0, $dppBeforeTotal - $dppAfterTotal), 2),
            'subtotal' => round($dppAfterTotal, 2),
            'ppn' => $ppn,
            'ppn_label' => 'Pajak ('.TaxAwarePrice::ppnLabel($taxPercent).')',
            'discount' => (float) ($order->discount_amount ?? 0),
            'discount_label' => self::discountLabel($order),
            'shipping' => (float) ($order->shipping_cost ?? 0),
            'service_fee' => (float) ($order->payment_fee ?? 0),
            'total' => (float) $order->total_amount,
        ];
    }

    public static function invoiceNumber(Order $order): string
    {
        $number = preg_replace('/[^A-Z0-9]/', '', (string) $order->order_number) ?: 'INV';
        $suffix = $order->created_at?->format('m-y') ?: now()->format('m-y');

        return 'RG-'.$number.'-'.$suffix;
    }

    protected static function billName(Order $order): string
    {
        $address = $order->address;

        return trim((string) (
            $address?->store_name
            ?: $address?->recipient_name
            ?: $order->user?->name
            ?: 'Customer'
        ));
    }

    /**
     * @return list<string>
     */
    protected static function fromLines(?Warehouse $warehouse): array
    {
        if (! $warehouse) {
            return ['Rasa Group'];
        }

        return self::uniqueLines([
            $warehouse->name,
            $warehouse->address,
            $warehouse->district_name ? 'Kec. '.$warehouse->district_name : null,
            $warehouse->regency_name,
            $warehouse->province_name,
            $warehouse->postal_code,
        ]);
    }

    /**
     * @return list<string>
     */
    protected static function billLines(Order $order): array
    {
        $address = $order->address;
        if ($address instanceof Address) {
            return self::uniqueLines([
                $address->address_detail,
                $address->district_name ? 'Kec. '.$address->district_name : null,
                $address->regency_name,
                $address->province_name,
                $address->postal_code,
            ]);
        }

        $fallback = preg_split('/\r\n|\n/', (string) $order->shipping_address) ?: [];
        $billName = strtolower(self::billName($order));

        return self::uniqueLines(array_filter($fallback, function ($line) use ($billName) {
            $line = trim((string) $line);

            return $line !== '' && strtolower($line) !== $billName;
        }));
    }

    protected static function discountLabel(Order $order): ?string
    {
        $amount = (float) ($order->discount_amount ?? 0);
        if ($amount <= 0) {
            return null;
        }

        $percent = (float) ($order->discount_percent ?? 0);
        if ($percent > 0) {
            $formatted = rtrim(rtrim(number_format($percent, 1, ',', '.'), '0'), ',');

            return 'Discount '.$formatted.'%';
        }

        return 'Discount';
    }

    /**
     * @param  list<mixed>  $lines
     * @return list<string>
     */
    protected static function uniqueLines(array $lines): array
    {
        $clean = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || in_array($line, $clean, true)) {
                continue;
            }
            $clean[] = $line;
        }

        return $clean;
    }
}

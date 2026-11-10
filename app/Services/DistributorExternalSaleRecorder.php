<?php

namespace App\Services;

use App\Models\DistributorApiClient;
use App\Models\DistributorExternalSale;
use App\Models\Product;
use App\Support\Wib;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DistributorExternalSaleRecorder
{
    /**
     * Simpan atau perbarui penjualan berdasarkan nomor order distributor.
     *
     * @return array{sale: DistributorExternalSale, created: bool}
     */
    public function record(DistributorApiClient $client, array $data, array $rawPayload): array
    {
        $soldAt = Carbon::parse($data['sold_at'], Wib::TZ);
        $items = $this->buildItems($data['items']);

        return DB::transaction(function () use ($client, $data, $rawPayload, $soldAt, $items) {
            $sale = DistributorExternalSale::updateOrCreate(
                [
                    'distributor_id' => $client->user_id,
                    'external_order_number' => $data['external_order_number'],
                ],
                [
                    'api_client_id' => $client->id,
                    'sold_at' => $soldAt->copy()->setTimezone(config('app.timezone')),
                    'sold_date' => $soldAt->copy()->setTimezone(Wib::TZ)->toDateString(),
                    'status' => $data['status'] ?? DistributorExternalSale::STATUS_CONFIRMED,
                    'customer_name' => $data['customer']['name'],
                    'customer_code' => $data['customer']['code'] ?? null,
                    'subtotal' => array_sum(array_column($items, 'subtotal')),
                    'discount' => $data['discount'] ?? 0,
                    'tax' => $data['tax'] ?? 0,
                    'total' => $data['total'],
                    'raw_payload' => $rawPayload,
                ]
            );

            $sale->items()->delete();
            $sale->items()->createMany($items);

            return ['sale' => $sale->load('items'), 'created' => $sale->wasRecentlyCreated];
        });
    }

    private function buildItems(array $rawItems): array
    {
        $productIds = $this->productIdsByCode();

        return array_map(fn (array $item) => [
            'external_product_code' => $item['product_code'],
            'product_name' => $item['product_name'] ?? null,
            'product_id' => $productIds[strtoupper(trim($item['product_code']))] ?? null,
            'quantity' => $item['quantity'],
            'uom' => $item['uom'] ?? null,
            'unit_price' => $item['unit_price'],
            'subtotal' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
        ], $rawItems);
    }

    /**
     * @return array<string, string> kode produk (kode utama dan alternatif) => id produk
     */
    private function productIdsByCode(): array
    {
        $map = [];

        foreach (Product::query()->get(['id', 'code', 'alternate_codes']) as $product) {
            foreach ($product->getAllCodes() as $code) {
                $map[strtoupper(trim((string) $code))] ??= $product->id;
            }
        }

        return $map;
    }
}

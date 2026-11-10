<?php

namespace App\Services;

use App\Models\DistributorExternalSale;
use App\Models\DistributorExternalSaleItem;
use App\Models\Order;
use App\Support\Wib;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DistributorExternalSalesReport
{
    /**
     * @return array{start_date: string, end_date: string, distributor_id: ?string, province_id: ?string}
     */
    public function parseFilters(array $input): array
    {
        $today = now(Wib::TZ);

        return [
            'start_date' => $this->dateOr($input['start_date'] ?? null, $today->copy()->startOfMonth()),
            'end_date' => $this->dateOr($input['end_date'] ?? null, $today),
            'distributor_id' => ($input['distributor_id'] ?? null) ?: null,
            'province_id' => ($input['province_id'] ?? null) ?: null,
        ];
    }

    /**
     * Semua penjualan sesuai filter, termasuk yang dibatalkan (untuk daftar transaksi).
     */
    public function salesQuery(array $filters): Builder
    {
        return DistributorExternalSale::query()
            ->whereBetween('sold_date', [$filters['start_date'], $filters['end_date']])
            ->when($filters['distributor_id'], fn ($q, $id) => $q->where('distributor_id', $id))
            ->when($filters['province_id'], fn ($q, $id) => $q->whereHas(
                'distributor.warehouse',
                fn ($warehouse) => $warehouse->where('province_id', $id)
            ));
    }

    public function summary(array $filters): array
    {
        $row = $this->confirmedQuery($filters)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as transactions, COUNT(DISTINCT distributor_id) as distributors')
            ->toBase()
            ->first();

        return [
            'total_sales' => (float) $row->total_sales,
            'transactions' => (int) $row->transactions,
            'distributors' => (int) $row->distributors,
            'cancelled' => (clone $this->salesQuery($filters))->where('status', DistributorExternalSale::STATUS_CANCELLED)->count(),
        ];
    }

    public function dailyTrend(array $filters): Collection
    {
        return $this->confirmedQuery($filters)
            ->selectRaw('sold_date, SUM(total) as total_sales, COUNT(*) as transactions')
            ->groupBy('sold_date')
            ->orderBy('sold_date')
            ->toBase()
            ->get();
    }

    /**
     * Penjualan distributor dibanding pembeliannya ke Rasa pada periode yang sama.
     * Keduanya tidak boleh dijumlahkan: barang yang sama akan terhitung dua kali.
     */
    public function byDistributor(array $filters): Collection
    {
        $rows = $this->confirmedQuery($filters)
            ->with('distributor:id,name,distributor_sales_code')
            ->selectRaw('distributor_id, SUM(total) as total_sales, COUNT(*) as transactions')
            ->groupBy('distributor_id')
            ->orderByDesc('total_sales')
            ->get();

        $purchases = Order::query()
            ->whereIn('user_id', $rows->pluck('distributor_id'))
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', $this->periodInAppTimezone($filters))
            ->selectRaw('user_id, SUM(total_amount) as total_purchases')
            ->groupBy('user_id')
            ->pluck('total_purchases', 'user_id');

        return $rows->map(fn ($row) => (object) [
            'name' => $row->distributor?->name ?? '-',
            'code' => $row->distributor?->distributor_sales_code,
            'total_sales' => (float) $row->total_sales,
            'transactions' => (int) $row->transactions,
            'total_purchases' => (float) ($purchases[$row->distributor_id] ?? 0),
        ]);
    }

    /**
     * Dikelompokkan per UOM karena qty dari sistem distributor bisa berbeda satuan.
     */
    public function topProducts(array $filters, int $limit = 10): Collection
    {
        return $this->confirmedItemsQuery($filters)
            ->whereNotNull('distributor_external_sale_items.product_id')
            ->join('products', 'products.id', '=', 'distributor_external_sale_items.product_id')
            ->selectRaw('products.code, products.name, distributor_external_sale_items.uom, SUM(distributor_external_sale_items.quantity) as quantity, SUM(distributor_external_sale_items.subtotal) as total_sales')
            ->groupBy('products.code', 'products.name', 'distributor_external_sale_items.uom')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->toBase()
            ->get();
    }

    /**
     * Kode produk yang belum dikenali, supaya datanya bisa dirapikan dengan distributor.
     */
    public function unmappedProducts(array $filters, int $limit = 20): Collection
    {
        return $this->confirmedItemsQuery($filters)
            ->whereNull('distributor_external_sale_items.product_id')
            ->selectRaw('external_product_code, MAX(distributor_external_sale_items.product_name) as product_name, SUM(distributor_external_sale_items.subtotal) as total_sales')
            ->groupBy('external_product_code')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->toBase()
            ->get();
    }

    private function confirmedQuery(array $filters): Builder
    {
        return $this->salesQuery($filters)->where('status', DistributorExternalSale::STATUS_CONFIRMED);
    }

    private function confirmedItemsQuery(array $filters): Builder
    {
        return DistributorExternalSaleItem::query()
            ->whereIn('sale_id', $this->confirmedQuery($filters)->select('id'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodInAppTimezone(array $filters): array
    {
        $timezone = config('app.timezone');

        return [
            Carbon::parse($filters['start_date'], Wib::TZ)->startOfDay()->setTimezone($timezone),
            Carbon::parse($filters['end_date'], Wib::TZ)->endOfDay()->setTimezone($timezone),
        ];
    }

    private function dateOr(?string $value, Carbon $fallback): string
    {
        try {
            return filled($value) ? Carbon::parse($value)->toDateString() : $fallback->toDateString();
        } catch (\Throwable) {
            return $fallback->toDateString();
        }
    }
}

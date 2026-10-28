<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GmvAnalyticsService
{
    /**
     * Parse date range and common filter parameters.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function parseFilters(array $filters): array
    {
        $startDate = !empty($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = !empty($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfDay();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $filters['status'] ?? 'valid', // valid, paid, all, completed
            'warehouse_id' => !empty($filters['warehouse_id']) && $filters['warehouse_id'] !== 'all' ? $filters['warehouse_id'] : null,
            'channel' => !empty($filters['channel']) && $filters['channel'] !== 'all' ? $filters['channel'] : null,
            'sales_code' => !empty($filters['sales_code']) && $filters['sales_code'] !== 'all' ? trim((string) $filters['sales_code']) : null,
            'user_role' => !empty($filters['user_role']) && $filters['user_role'] !== 'all' ? $filters['user_role'] : null,
        ];
    }

    /**
     * Base query for orders with applied filters.
     *
     * @param array<string, mixed> $parsedFilters
     * @return Builder
     */
    public function getOrdersBaseQuery(array $parsedFilters): Builder
    {
        $query = Order::query()
            ->whereBetween('created_at', [$parsedFilters['start_date'], $parsedFilters['end_date']]);

        // Status filter
        match ($parsedFilters['status']) {
            'paid' => $query->where('payment_status', 'paid'),
            'approved' => $query->where('finance_approved', true),
            'all' => $query, // Include cancelled
            default => $query->where('order_status', '!=', 'cancelled'), // 'valid'
        };

        // Warehouse filter
        if ($parsedFilters['warehouse_id']) {
            $query->where('source_warehouse_id', $parsedFilters['warehouse_id']);
        }

        // Sales code filter
        if ($parsedFilters['sales_code']) {
            $query->where('sales_code', $parsedFilters['sales_code']);
        }

        // Channel filter
        if ($parsedFilters['channel']) {
            if ($parsedFilters['channel'] === 'sales') {
                $query->whereNotNull('sales_code')->where('sales_code', '!=', '');
            } elseif ($parsedFilters['channel'] === 'online') {
                $query->where('order_type', 'regular');
            } elseif ($parsedFilters['channel'] === 'pos') {
                $query->where('order_type', 'pos');
            }
        }

        // User role filter
        if ($parsedFilters['user_role']) {
            $query->whereHas('user', function ($q) use ($parsedFilters) {
                $q->where('role', $parsedFilters['user_role']);
            });
        }

        return $query;
    }

    /**
     * Get executive summary metrics (GMV, Diskon, Net, Ongkir, PPN, AOV, Growth).
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $parsed = $this->parseFilters($filters);
        $ordersQuery = $this->getOrdersBaseQuery($parsed);

        // Agregasi order items untuk GMV murni (Item Price * Quantity) dan Catalog Gross GMV
        $orderIds = (clone $ordersQuery)->pluck('id');

        $itemStats = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('order_items.order_id', $orderIds)
            ->select(
                DB::raw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as gross_item_gmv'),
                DB::raw('COALESCE(SUM(COALESCE(products.price, order_items.price) * order_items.quantity), 0) as catalog_gross_gmv'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_units_sold')
            )
            ->first();

        // Agregasi order level (Total Amount, Shipping, Diskon, PPN)
        $orderStats = (clone $ordersQuery)->select(
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total_order_amount'),
            DB::raw('COALESCE(SUM(shipping_cost), 0) as total_shipping'),
            DB::raw('COALESCE(SUM(discount_amount), 0) as explicit_discounts'),
            DB::raw('COALESCE(SUM(payment_fee), 0) as total_payment_fees'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END), 0) as realized_revenue'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END), 0) as paid_orders_count'),
            DB::raw('COALESCE(SUM(CASE WHEN order_status = "cancelled" THEN 1 ELSE 0 END), 0) as cancelled_orders_count')
        )->first();

        $grossGmv = (float) ($itemStats->gross_item_gmv ?? 0);
        $catalogGmv = (float) ($itemStats->catalog_gross_gmv ?? 0);
        $totalOrders = (int) ($orderStats->total_orders ?? 0);
        $totalShipping = (float) ($orderStats->total_shipping ?? 0);
        $totalOrderAmount = (float) ($orderStats->total_order_amount ?? 0);
        $realizedRevenue = (float) ($orderStats->realized_revenue ?? 0);
        $paidOrdersCount = (int) ($orderStats->paid_orders_count ?? 0);

        // Hitung total diskon efektif (Diskon Kupon + Diskon Berjenjang/Kategori)
        // Diskon Efektif = Gross Item GMV - Net DPP yang dibebankan
        $effectiveDiscount = 0.0;
        $ordersList = (clone $ordersQuery)->with('items')->get(['id', 'total_amount', 'shipping_cost', 'payment_fee', 'discount_amount', 'pakai_ppn', 'user_id']);
        foreach ($ordersList as $o) {
            $itemGross = (float) $o->items->sum(fn ($it) => $it->price * $it->quantity);
            $goodsTotal = max(0.0, (float) $o->total_amount - (float) ($o->shipping_cost ?? 0) - (float) ($o->payment_fee ?? 0));
            $usesPpn = (bool) ($o->pakai_ppn || ($o->user && $o->user->usesPpn()));
            $netDpp = $usesPpn ? round($goodsTotal / 1.11, 2) : $goodsTotal;
            
            if ((float) ($o->discount_amount ?? 0) > 0) {
                $effectiveDiscount += (float) $o->discount_amount;
            } elseif ($itemGross > $netDpp) {
                $effectiveDiscount += round($itemGross - $netDpp, 2);
            }
        }

        $netMerchandiseValue = max(0.0, $grossGmv - $effectiveDiscount);
        $estimatedPpn = max(0.0, $totalOrderAmount - $netMerchandiseValue - $totalShipping);
        $aov = $totalOrders > 0 ? round($grossGmv / $totalOrders, 2) : 0.0;

        // Hitung Growth Rate terhadap periode sebelumnya dengan durasi hari yang sama
        $durationDays = $parsed['start_date']->diffInDays($parsed['end_date']) + 1;
        $prevStartDate = (clone $parsed['start_date'])->subDays($durationDays);
        $prevEndDate = (clone $parsed['start_date'])->subSecond();
        
        $prevParsed = array_merge($parsed, [
            'start_date' => $prevStartDate,
            'end_date' => $prevEndDate,
        ]);
        $prevOrderIds = $this->getOrdersBaseQuery($prevParsed)->pluck('id');
        $prevGmv = (float) (DB::table('order_items')
            ->whereIn('order_id', $prevOrderIds)
            ->select(DB::raw('COALESCE(SUM(price * quantity), 0) as gmv'))
            ->value('gmv') ?? 0);

        $growthPercentage = $prevGmv > 0
            ? round((($grossGmv - $prevGmv) / $prevGmv) * 100, 1)
            : ($grossGmv > 0 ? 100.0 : 0.0);

        return [
            'period' => [
                'start_date' => $parsed['start_date']->format('Y-m-d'),
                'end_date' => $parsed['end_date']->format('Y-m-d'),
                'label' => $parsed['start_date']->format('d M Y') . ' - ' . $parsed['end_date']->format('d M Y'),
                'days' => $durationDays,
            ],
            'gross_gmv' => $grossGmv,
            'catalog_gmv' => $catalogGmv,
            'effective_discount' => $effectiveDiscount,
            'net_merchandise_value' => $netMerchandiseValue,
            'total_shipping' => $totalShipping,
            'estimated_ppn' => $estimatedPpn,
            'total_order_amount' => $totalOrderAmount,
            'realized_revenue' => $realizedRevenue,
            'total_orders' => $totalOrders,
            'paid_orders_count' => $paidOrdersCount,
            'cancelled_orders_count' => (int) ($orderStats->cancelled_orders_count ?? 0),
            'total_units_sold' => (int) ($itemStats->total_units_sold ?? 0),
            'aov' => $aov,
            'prev_period_gmv' => $prevGmv,
            'growth_percentage' => $growthPercentage,
        ];
    }

    /**
     * Get daily trend of GMV and Realized Revenue for Line Chart.
     *
     * @param array<string, mixed> $filters
     * @return array{labels: list<string>, gmv: list<float>, revenue: list<float>, orders: list<int>}
     */
    public function getDailyTrend(array $filters = []): array
    {
        $parsed = $this->parseFilters($filters);
        $ordersQuery = $this->getOrdersBaseQuery($parsed);

        // Fetch daily grouped stats
        $dailyData = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.id', (clone $ordersQuery)->pluck('id'))
            ->select(
                DB::raw('DATE(orders.created_at) as order_date'),
                DB::raw('SUM(order_items.price * order_items.quantity) as daily_gmv'),
                DB::raw('SUM(DISTINCT CASE WHEN orders.payment_status = "paid" THEN orders.total_amount ELSE 0 END) as daily_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as daily_orders')
            )
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get()
            ->keyBy('order_date');

        $labels = [];
        $gmvSeries = [];
        $revenueSeries = [];
        $ordersSeries = [];

        $current = (clone $parsed['start_date'])->copy();
        $end = (clone $parsed['end_date'])->copy();

        while ($current->lte($end)) {
            $dateKey = $current->format('Y-m-d');
            $labels[] = $current->format('d M');

            $row = $dailyData->get($dateKey);
            $gmvSeries[] = $row ? round((float) $row->daily_gmv, 2) : 0.0;
            $revenueSeries[] = $row ? round((float) $row->daily_revenue, 2) : 0.0;
            $ordersSeries[] = $row ? (int) $row->daily_orders : 0;

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'gmv' => $gmvSeries,
            'revenue' => $revenueSeries,
            'orders' => $ordersSeries,
        ];
    }

    /**
     * Get breakdown of GMV per Warehouse / Hub.
     *
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function getWarehouseBreakdown(array $filters = []): array
    {
        $parsed = $this->parseFilters($filters);
        $ordersQuery = $this->getOrdersBaseQuery($parsed);

        $results = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('warehouses', 'orders.source_warehouse_id', '=', 'warehouses.id')
            ->whereIn('orders.id', (clone $ordersQuery)->pluck('id'))
            ->select(
                DB::raw('COALESCE(warehouses.name, "Unassigned / Lainnya") as warehouse_name'),
                'orders.source_warehouse_id',
                DB::raw('SUM(order_items.price * order_items.quantity) as gmv'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('orders.source_warehouse_id', 'warehouses.name')
            ->orderByDesc('gmv')
            ->get();

        $totalGmv = $results->sum('gmv');

        return $results->map(function ($row) use ($totalGmv) {
            $gmv = (float) $row->gmv;
            return [
                'warehouse_id' => $row->source_warehouse_id,
                'name' => $row->warehouse_name,
                'gmv' => $gmv,
                'gmv_formatted' => 'Rp ' . number_format($gmv, 0, ',', '.'),
                'orders_count' => (int) $row->orders_count,
                'share_percentage' => $totalGmv > 0 ? round(($gmv / $totalGmv) * 100, 1) : 0.0,
            ];
        })->toArray();
    }

    /**
     * Get Top Products contributing to GMV.
     *
     * @param array<string, mixed> $filters
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getTopProducts(array $filters = [], int $limit = 10): array
    {
        $parsed = $this->parseFilters($filters);
        $ordersQuery = $this->getOrdersBaseQuery($parsed);

        $results = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('order_items.order_id', (clone $ordersQuery)->pluck('id'))
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                DB::raw('SUM(order_items.price * order_items.quantity) as gmv'),
                DB::raw('SUM(order_items.quantity) as total_qty')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('gmv')
            ->limit($limit)
            ->get();

        return $results->map(function ($row) {
            $gmv = (float) $row->gmv;
            return [
                'product_id' => $row->product_id,
                'name' => $row->product_name,
                'code' => $row->product_code,
                'gmv' => $gmv,
                'gmv_formatted' => 'Rp ' . number_format($gmv, 0, ',', '.'),
                'total_qty' => (int) $row->total_qty,
            ];
        })->toArray();
    }

    /**
     * Get Top Sales Codes by GMV.
     *
     * @param array<string, mixed> $filters
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getTopSales(array $filters = [], int $limit = 10): array
    {
        $parsed = $this->parseFilters($filters);
        $ordersQuery = $this->getOrdersBaseQuery($parsed);

        $results = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('users', 'orders.sales_code', '=', 'users.sales_code')
            ->whereIn('orders.id', (clone $ordersQuery)->pluck('id'))
            ->whereNotNull('orders.sales_code')
            ->where('orders.sales_code', '!=', '')
            ->select(
                'orders.sales_code',
                DB::raw('COALESCE(users.name, orders.sales_code) as salesperson_name'),
                DB::raw('SUM(order_items.price * order_items.quantity) as gmv'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('orders.sales_code', 'users.name')
            ->orderByDesc('gmv')
            ->limit($limit)
            ->get();

        return $results->map(function ($row) {
            $gmv = (float) $row->gmv;
            return [
                'sales_code' => $row->sales_code,
                'salesperson_name' => $row->salesperson_name,
                'gmv' => $gmv,
                'gmv_formatted' => 'Rp ' . number_format($gmv, 0, ',', '.'),
                'orders_count' => (int) $row->orders_count,
            ];
        })->toArray();
    }
}

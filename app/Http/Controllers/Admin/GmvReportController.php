<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GmvReportExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GmvAnalyticsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class GmvReportController extends Controller
{
    protected GmvAnalyticsService $analyticsService;

    public function __construct(GmvAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display GMV Analytics page or return JSON for AJAX filter update.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'start_date',
            'end_date',
            'status',
            'warehouse_id',
            'channel',
            'sales_code',
            'user_role',
        ]);

        if ($request->ajax() && !$request->has('draw')) {
            $metrics = $this->analyticsService->getSummaryMetrics($filters);
            $trend = $this->analyticsService->getDailyTrend($filters);
            $warehouseBreakdown = $this->analyticsService->getWarehouseBreakdown($filters);
            $topProducts = $this->analyticsService->getTopProducts($filters, 5);
            $topSales = $this->analyticsService->getTopSales($filters, 5);

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'trend' => $trend,
                'warehouse_breakdown' => $warehouseBreakdown,
                'top_products' => $topProducts,
                'top_sales' => $topSales,
            ]);
        }

        $metrics = $this->analyticsService->getSummaryMetrics($filters);
        $trend = $this->analyticsService->getDailyTrend($filters);
        $warehouseBreakdown = $this->analyticsService->getWarehouseBreakdown($filters);
        $topProducts = $this->analyticsService->getTopProducts($filters, 5);
        $topSales = $this->analyticsService->getTopSales($filters, 5);

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name', 'kode_hub']);
        $salesList = User::where('role', User::ROLE_SALES)->whereNotNull('sales_code')->orderBy('name')->get(['id', 'name', 'sales_code']);

        return view('admin.reports.gmv', compact(
            'metrics',
            'trend',
            'warehouseBreakdown',
            'topProducts',
            'topSales',
            'warehouses',
            'salesList',
            'filters'
        ));
    }

    /**
     * AJAX DataTables feed for GMV Transactions.
     */
    public function datatable(Request $request)
    {
        $filters = $request->only([
            'start_date',
            'end_date',
            'status',
            'warehouse_id',
            'channel',
            'sales_code',
            'user_role',
        ]);

        $parsed = $this->analyticsService->parseFilters($filters);
        $query = $this->analyticsService->getOrdersBaseQuery($parsed)
            ->with(['items', 'user', 'sourceWarehouse'])
            ->select('orders.*');

        return DataTables::eloquent($query)
            ->addColumn('customer_name', function ($order) {
                return $order->user ? $order->user->name : 'Tamu';
            })
            ->addColumn('warehouse_name', function ($order) {
                return $order->sourceWarehouse ? $order->sourceWarehouse->name : '-';
            })
            ->addColumn('gross_gmv', function ($order) {
                return (float) $order->items->sum(fn ($it) => $it->price * $it->quantity);
            })
            ->addColumn('discount_val', function ($order) {
                $grossDpp = (float) $order->items->sum(fn ($it) => $it->price * $it->quantity);
                $shipping = (float) ($order->shipping_cost ?? 0);
                $totalAmount = (float) $order->total_amount;
                $goodsTotal = max(0.0, $totalAmount - $shipping - (float) ($order->payment_fee ?? 0));
                $usesPpn = (bool) ($order->pakai_ppn || ($order->user && $order->user->usesPpn()));
                $netDpp = $usesPpn ? round($goodsTotal / 1.11, 2) : $goodsTotal;

                if ((float) ($order->discount_amount ?? 0) > 0) {
                    return (float) $order->discount_amount;
                }
                return max(0.0, round($grossDpp - $netDpp, 2));
            })
            ->editColumn('gross_gmv', function ($order) {
                $gmv = (float) $order->items->sum(fn ($it) => $it->price * $it->quantity);
                return 'Rp ' . number_format($gmv, 0, ',', '.');
            })
            ->editColumn('discount_amount', function ($order) {
                $grossDpp = (float) $order->items->sum(fn ($it) => $it->price * $it->quantity);
                $shipping = (float) ($order->shipping_cost ?? 0);
                $totalAmount = (float) $order->total_amount;
                $goodsTotal = max(0.0, $totalAmount - $shipping - (float) ($order->payment_fee ?? 0));
                $usesPpn = (bool) ($order->pakai_ppn || ($order->user && $order->user->usesPpn()));
                $netDpp = $usesPpn ? round($goodsTotal / 1.11, 2) : $goodsTotal;

                $disc = (float) ($order->discount_amount ?? 0);
                if ($disc <= 0 && $grossDpp > $netDpp) {
                    $disc = round($grossDpp - $netDpp, 2);
                }
                return $disc > 0 ? 'Rp ' . number_format($disc, 0, ',', '.') : '-';
            })
            ->editColumn('shipping_cost', function ($order) {
                return (float) ($order->shipping_cost ?? 0) > 0
                    ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.')
                    : '-';
            })
            ->editColumn('total_amount', function ($order) {
                return '<strong>Rp ' . number_format($order->total_amount, 0, ',', '.') . '</strong>';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('d/m/Y H:i');
            })
            ->addColumn('status_badge', function ($order) {
                $ps = $order->payment_status;
                $badge = match ($ps) {
                    'paid' => '<span class="label label-success">Lunas</span>',
                    'pending' => '<span class="label label-warning">Pending</span>',
                    'term_of_payment' => '<span class="label label-info">TOP</span>',
                    default => '<span class="label label-default">' . ucfirst((string) $ps) . '</span>',
                };
                if ($order->order_status === 'cancelled') {
                    $badge .= ' <span class="label label-danger">Batal</span>';
                }
                return $badge;
            })
            ->addColumn('action', function ($order) {
                $url = route('admin.orders.show', $order);
                return '<a href="' . $url . '" class="btn btn-xs btn-primary" title="Lihat Detail" target="_blank"><i class="fa fa-eye"></i> Detail</a>';
            })
            ->rawColumns(['total_amount', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Export GMV report to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'start_date',
            'end_date',
            'status',
            'warehouse_id',
            'channel',
            'sales_code',
            'user_role',
        ]);

        $filename = 'Laporan-GMV-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new GmvReportExport($filters), $filename);
    }
}

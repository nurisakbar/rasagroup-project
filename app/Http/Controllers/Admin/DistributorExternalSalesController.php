<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DistributorExternalSalesExport;
use App\Http\Controllers\Controller;
use App\Models\DistributorExternalSale;
use App\Models\User;
use App\Models\WilayahAdministratif;
use App\Services\DistributorExternalSalesReport;
use App\Support\Wib;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DistributorExternalSalesController extends Controller
{
    /** Data penjualan antar-distributor sensitif; middleware admin saja terlalu longgar. */
    public const ALLOWED_ROLES = [User::ROLE_SUPER_ADMIN, 'finance'];

    public function __construct(private DistributorExternalSalesReport $report)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();
        $filters = $this->report->parseFilters($request->query());

        return view('admin.reports.distributor-sales.index', [
            'filters' => $filters,
            'summary' => $this->report->summary($filters),
            'trend' => $this->report->dailyTrend($filters),
            'distributorRows' => $this->report->byDistributor($filters),
            'topProducts' => $this->report->topProducts($filters),
            'unmappedProducts' => $this->report->unmappedProducts($filters),
            'distributors' => User::where('role', User::ROLE_DISTRIBUTOR)->orderBy('name')->get(['id', 'name']),
            'provinces' => WilayahAdministratif::select('province_id as id', 'province_name as name')->distinct()->orderBy('province_name')->get(),
        ]);
    }

    public function datatable(Request $request)
    {
        $this->authorizeAccess();
        $filters = $this->report->parseFilters($request->query());

        return DataTables::of($this->report->salesQuery($filters)->with('distributor'))
            ->addColumn('distributor_name', fn ($sale) => e($sale->distributor?->name ?? '-'))
            ->editColumn('external_order_number', fn ($sale) => '<a href="' . route('admin.reports.distributor-sales.show', $sale) . '">' . e($sale->external_order_number) . '</a>')
            ->editColumn('customer_name', fn ($sale) => e($sale->customer_name))
            ->editColumn('sold_at', fn ($sale) => Wib::format($sale->sold_at))
            ->editColumn('total', fn ($sale) => 'Rp ' . number_format((float) $sale->total, 0, ',', '.'))
            ->editColumn('status', fn ($sale) => $sale->status === DistributorExternalSale::STATUS_CANCELLED
                ? '<span class="label label-danger">Dibatalkan</span>'
                : '<span class="label label-success">Terkonfirmasi</span>')
            ->rawColumns(['external_order_number', 'status'])
            ->make(true);
    }

    public function show(DistributorExternalSale $sale)
    {
        $this->authorizeAccess();
        $sale->load(['distributor', 'items.product']);

        return view('admin.reports.distributor-sales.show', compact('sale'));
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();
        $filters = $this->report->parseFilters($request->query());
        $filename = "penjualan-distributor-{$filters['start_date']}-{$filters['end_date']}.xlsx";

        return Excel::download(new DistributorExternalSalesExport($this->report->salesQuery($filters)), $filename);
    }

    private function authorizeAccess(): void
    {
        abort_unless(in_array(auth('admin')->user()?->role, self::ALLOWED_ROLES, true), 403);
    }
}

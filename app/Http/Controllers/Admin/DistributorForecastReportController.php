<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DistributorForecastReportExport;
use App\Http\Controllers\Controller;
use App\Models\DistributorForecast;
use App\Models\DistributorForecastLine;
use App\Models\User;
use App\Services\DistributorForecastService;
use App\Support\Wib;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * Laporan forecast kebutuhan dari semua distributor (per outlet rekrutan × produk × bulan).
 */
class DistributorForecastReportController extends Controller
{

    public function index(Request $request)
    {
        $this->authorizeAccess();
        [$year, $distributorId] = $this->filters($request);

        if ($request->ajax()) {
            return DataTables::of($this->query($year, $distributorId))
                ->addIndexColumn()
                ->addColumn('distributor', fn ($line) => e($line->forecast->distributor->name ?? '-'))
                ->addColumn('customer', fn ($line) => e($line->outlet->name ?? '-'))
                ->addColumn('item_number', fn ($line) => e($line->product->code ?? '-'))
                ->addColumn('description', fn ($line) => e($line->product->name ?? '-'))
                ->addColumn('brand', fn ($line) => e($line->product->brand->name ?? '-'))
                ->addColumn('total', fn ($line) => number_format($line->total(), 0, ',', '.'))
                ->editColumn('qty_1', fn ($l) => number_format($l->qty_1, 0, ',', '.'))
                ->editColumn('qty_2', fn ($l) => number_format($l->qty_2, 0, ',', '.'))
                ->editColumn('qty_3', fn ($l) => number_format($l->qty_3, 0, ',', '.'))
                ->editColumn('qty_4', fn ($l) => number_format($l->qty_4, 0, ',', '.'))
                ->editColumn('qty_5', fn ($l) => number_format($l->qty_5, 0, ',', '.'))
                ->editColumn('qty_6', fn ($l) => number_format($l->qty_6, 0, ',', '.'))
                ->editColumn('qty_7', fn ($l) => number_format($l->qty_7, 0, ',', '.'))
                ->editColumn('qty_8', fn ($l) => number_format($l->qty_8, 0, ',', '.'))
                ->editColumn('qty_9', fn ($l) => number_format($l->qty_9, 0, ',', '.'))
                ->editColumn('qty_10', fn ($l) => number_format($l->qty_10, 0, ',', '.'))
                ->editColumn('qty_11', fn ($l) => number_format($l->qty_11, 0, ',', '.'))
                ->editColumn('qty_12', fn ($l) => number_format($l->qty_12, 0, ',', '.'))
                ->filter(function ($query) use ($request) {
                    $keyword = trim((string) $request->input('search.value'));
                    if ($keyword === '') {
                        return;
                    }
                    $like = '%' . addcslashes($keyword, '\\%_') . '%';
                    $query->where(function ($q) use ($like) {
                        $q->whereHas('outlet', fn ($o) => $o->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('qad_customer_code', 'like', $like))
                            ->orWhereHas('product', fn ($p) => $p->withoutGlobalScopes()->where('code', 'like', $like)->orWhere('name', 'like', $like))
                            ->orWhereHas('forecast.distributor', fn ($d) => $d->where('name', 'like', $like));
                    });
                })
                ->make(true);
        }

        $totals = DistributorForecastLine::query()
            ->whereHas('forecast', fn ($q) => $q->where('year', $year)->when($distributorId, fn ($q) => $q->where('distributor_id', $distributorId)))
            ->selectRaw(implode(', ', array_map(fn ($m) => "COALESCE(SUM(qty_{$m}),0) as m{$m}", range(1, 12))))
            ->toBase()->first();
        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)->orderBy('name')->get(['id', 'name', 'distributor_sales_code']);
        $years = DistributorForecast::query()->distinct()->orderByDesc('year')->pluck('year')
            ->merge(DistributorForecast::selectableYears())->unique()->sortDesc()->values();

        return view('admin.reports.distributor-forecast.index', compact('year', 'distributorId', 'distributors', 'years', 'totals'));
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();
        [$year, $distributorId] = $this->filters($request);

        return Excel::download(new DistributorForecastReportExport($this->query($year, $distributorId)->get()), "forecast_distributor_{$year}.xlsx");
    }

    private function query(int $year, ?string $distributorId)
    {
        return DistributorForecastLine::query()
            ->with(['forecast.distributor:id,name,sales_code', 'outlet:id,name,email,qad_customer_code', 'product:id,code,name,brand_id', 'product.brand:id,name'])
            ->whereHas('forecast', fn ($q) => $q->where('year', $year)->when($distributorId, fn ($q) => $q->where('distributor_id', $distributorId)))
            ->select('distributor_forecast_lines.*');
    }

    /** @return array{0: int, 1: ?string} */
    private function filters(Request $request): array
    {
        $year = (int) ($request->query('year') ?: now(Wib::TZ)->year);
        $year = max(2000, min(2100, $year));
        $distributorId = $request->query('distributor_id') ?: null;

        return [$year, $distributorId];
    }

    private function authorizeAccess(): void
    {
        // Hak akses dijaga middleware modul (EnsureAdminModuleAccess); tidak ada pengecekan role di sini.
    }
}

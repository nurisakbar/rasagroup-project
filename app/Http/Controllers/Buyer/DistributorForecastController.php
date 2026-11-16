<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\DistributorForecastExport;
use App\Http\Controllers\Controller;
use App\Imports\DistributorForecastImport;
use App\Models\DistributorForecast;
use App\Models\DistributorForecastLine;
use App\Models\Product;
use App\Models\User;
use App\Services\DistributorForecastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Forecast kebutuhan distributor per outlet rekrutan × produk × bulan (satuan botol/pcs).
 */
class DistributorForecastController extends Controller
{
    public function __construct(private DistributorForecastService $service)
    {
    }

    public function index(Request $request): View
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $forecast = $this->service->forecastFor($distributor, $year);

        $lines = $forecast->lines()->with(['outlet:id,name,email,qad_customer_code', 'product:id,code,name,brand_id,unit', 'product.brand:id,name'])->get()
            ->sortBy(fn ($line) => strtolower(($line->outlet->name ?? '') . '|' . ($line->product->code ?? '')))
            ->values();
        $outlets = $this->service->outlets($distributor);
        $products = Product::where('status', 'active')->whereNotNull('code')->orderBy('code')->get(['id', 'code', 'name']);
        $totals = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => $lines->sum(fn ($line) => $line->qty($m))]);

        return view('buyer.forecast.index', [
            'forecast' => $forecast,
            'year' => $year,
            'years' => DistributorForecast::selectableYears(),
            'lines' => $lines,
            'outlets' => $outlets,
            'products' => $products,
            'totals' => $totals,
            'salesName' => DistributorForecastService::salesName($distributor),
            'service' => $this->service,
        ]);
    }

    /** Tambah baris outlet × produk (atau isi baris yang sudah ada). */
    public function store(Request $request): RedirectResponse
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $outletIds = $this->service->outlets($distributor)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'outlet_id' => ['required', Rule::in($outletIds)],
            'product_id' => ['required', Rule::exists('products', 'id')],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:99999999'],
        ], [
            'outlet_id.in' => 'Outlet harus outlet rekrutan Anda.',
        ]);

        $forecast = $this->service->forecastFor($distributor, $year);
        $result = $this->service->saveLine(
            $forecast,
            User::findOrFail($validated['outlet_id']),
            Product::withoutGlobalScopes()->findOrFail($validated['product_id']),
            $validated['qty'] ?? [],
            Auth::id(),
            'web'
        );

        return redirect()->route('buyer.forecast.index', ['year' => $year])
            ->with('success', 'Baris forecast disimpan.' . $this->lockedNote($result['ignored_locked']));
    }

    /** Simpan semua angka di tabel sekaligus. */
    public function update(Request $request): RedirectResponse
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $forecast = $this->service->forecastFor($distributor, $year);

        $validated = $request->validate([
            'lines' => ['nullable', 'array'],
            'lines.*' => ['array'],
            'lines.*.*' => ['nullable', 'integer', 'min:0', 'max:99999999'],
        ]);

        $lines = $forecast->lines()->with(['outlet', 'product'])->get()->keyBy(fn ($line) => (string) $line->id);
        $changed = 0;
        $ignored = [];
        foreach ($validated['lines'] ?? [] as $lineId => $quantities) {
            $line = $lines[(string) $lineId] ?? null;
            if (! $line) {
                continue;
            }
            $result = $this->service->saveLine($forecast, $line->outlet, $line->product, $quantities, Auth::id(), 'web');
            $changed += $result['changed'];
            $ignored = array_merge($ignored, $result['ignored_locked']);
        }

        return redirect()->route('buyer.forecast.index', ['year' => $year])
            ->with('success', $changed > 0 ? "Forecast disimpan ({$changed} angka berubah)." . $this->lockedNote($ignored) : 'Tidak ada perubahan.');
    }

    public function destroy(Request $request, DistributorForecastLine $line): RedirectResponse
    {
        $distributor = $this->distributor();
        abort_unless((string) $line->forecast->distributor_id === (string) $distributor->id, 404);

        if (! $this->service->canDelete($line)) {
            return back()->with('error', 'Baris ini tidak bisa dihapus karena berisi angka di bulan yang sudah berjalan.');
        }

        $year = $line->forecast->year;
        $this->service->deleteLine($line, Auth::id());

        return redirect()->route('buyer.forecast.index', ['year' => $year])->with('success', 'Baris forecast dihapus.');
    }

    public function export(Request $request)
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $forecast = $this->service->forecastFor($distributor, $year);

        return Excel::download(new DistributorForecastExport($forecast), "forecast_{$distributor->distributor_sales_code}_{$year}.xlsx");
    }

    public function import(Request $request): RedirectResponse
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $forecast = $this->service->forecastFor($distributor, $year);
        $import = new DistributorForecastImport($forecast, $distributor, Auth::id());
        Excel::import($import, $request->file('file'));

        if ($import->errors) {
            return redirect()->route('buyer.forecast.index', ['year' => $year])
                ->with('error', 'Upload dibatalkan, tidak ada data yang disimpan. Perbaiki lalu upload ulang.')
                ->with('import_errors', array_slice($import->errors, 0, 50));
        }

        $note = $import->ignoredLocked > 0 ? " {$import->ignoredLocked} angka di bulan yang sudah berjalan diabaikan." : '';

        return redirect()->route('buyer.forecast.index', ['year' => $year])
            ->with('success', "Upload berhasil: {$import->saved} baris diproses, {$import->changedCells} angka berubah.{$note}");
    }

    private function distributor(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->isDistributor(), 403);

        return $user;
    }

    private function year(Request $request): int
    {
        $years = DistributorForecast::selectableYears();
        $year = (int) $request->input('year', $years[0]);

        return in_array($year, $years, true) ? $year : $years[0];
    }

    private function lockedNote(array $months): string
    {
        $months = array_unique($months);

        return $months
            ? ' Bulan ' . implode(', ', array_map(fn ($m) => DistributorForecast::MONTH_LABELS[$m], $months)) . ' sudah berjalan sehingga tidak diubah.'
            : '';
    }
}

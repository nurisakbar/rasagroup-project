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

        $allLines = $forecast->lines()->with(['product:id,code,name,brand_id,unit', 'product.brand:id,name'])->get();
        $outlets = $this->service->outlets($distributor);

        // Subdist yang sedang diisi: pilihan user, selain itu subdist pertama yang sudah punya baris, selain itu yang pertama.
        $selectedId = (string) $request->input('outlet', '');
        if (! $outlets->contains(fn ($o) => (string) $o->id === $selectedId)) {
            $withLines = $allLines->pluck('outlet_id')->map(fn ($id) => (string) $id)->unique();
            $selectedId = (string) ($outlets->first(fn ($o) => $withLines->contains((string) $o->id))?->id ?? $outlets->first()?->id ?? '');
        }
        $selectedOutlet = $outlets->first(fn ($o) => (string) $o->id === $selectedId);

        $lines = $allLines->filter(fn ($line) => (string) $line->outlet_id === $selectedId)
            ->sortBy(fn ($line) => strtolower($line->product->code ?? ''))
            ->values();

        // Ringkasan per subdist untuk pilihan di langkah 1.
        $outletSummary = $allLines->groupBy(fn ($line) => (string) $line->outlet_id)
            ->map(fn ($group) => ['products' => $group->count(), 'units' => $group->sum(fn ($line) => $line->total())]);

        $addedProductIds = $lines->pluck('product_id')->all();
        $products = Product::where('status', 'active')->whereNotNull('code')
            ->whereNotIn('id', $addedProductIds)->orderBy('code')->get(['id', 'code', 'name']);
        $totals = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => $lines->sum(fn ($line) => $line->qty($m))]);

        return view('buyer.forecast.index', [
            'forecast' => $forecast,
            'year' => $year,
            'years' => DistributorForecast::selectableYears(),
            'lines' => $lines,
            'outlets' => $outlets,
            'selectedOutlet' => $selectedOutlet,
            'outletSummary' => $outletSummary,
            'grandUnits' => $allLines->sum(fn ($line) => $line->total()),
            'products' => $products,
            'totals' => $totals,
            'salesName' => DistributorForecastService::salesName($distributor),
            'service' => $this->service,
        ]);
    }

    /** Tambah satu atau beberapa produk ke forecast sebuah subdist (baris dibuat kosong bila belum ada). */
    public function store(Request $request): RedirectResponse
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $outletIds = $this->service->outlets($distributor)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'outlet_id' => ['required', Rule::in($outletIds)],
            'product_id' => ['nullable', 'required_without:product_ids', Rule::exists('products', 'id')],
            'product_ids' => ['nullable', 'required_without:product_id', 'array', 'max:200'],
            'product_ids.*' => [Rule::exists('products', 'id')],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:99999999'],
        ], [
            'outlet_id.in' => 'Subdist harus subdist rekrutan Anda.',
            'product_id.required_without' => 'Pilih minimal satu produk.',
            'product_ids.required_without' => 'Pilih minimal satu produk.',
        ]);

        $outlet = User::findOrFail($validated['outlet_id']);
        $productIds = collect($validated['product_ids'] ?? [])->when(! empty($validated['product_id']), fn ($c) => $c->push($validated['product_id']))->unique()->values();
        $forecast = $this->service->forecastFor($distributor, $year);

        $ignored = [];
        foreach ($productIds as $productId) {
            $result = $this->service->saveLine(
                $forecast,
                $outlet,
                Product::withoutGlobalScopes()->findOrFail($productId),
                $validated['qty'] ?? [],
                Auth::id(),
                'web'
            );
            $ignored = array_merge($ignored, $result['ignored_locked']);
        }

        $count = $productIds->count();

        return $this->toIndex($year, $outlet->id)
            ->with('success', ($count > 1 ? "{$count} produk ditambahkan" : 'Produk ditambahkan') . " ke forecast {$outlet->name}. Isi jumlah per bulan lalu simpan." . $this->lockedNote($ignored));
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

        return $this->toIndex($year, $request->input('outlet'))
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
        $outletId = $line->outlet_id;
        $this->service->deleteLine($line, Auth::id());

        return $this->toIndex($year, $outletId)->with('success', 'Produk dihapus dari forecast.');
    }

    /** Unduh template berisi semua produk master: subdist terpilih (?outlet=) atau semua subdist (tanpa ?outlet). */
    public function export(Request $request)
    {
        $distributor = $this->distributor();
        $year = $this->year($request);
        $forecast = $this->service->forecastFor($distributor, $year);

        $outlet = null;
        if ($request->filled('outlet')) {
            $outlet = $this->service->outlets($distributor)->first(fn ($o) => (string) $o->id === (string) $request->input('outlet'));
            abort_unless($outlet, 404);
        }

        $suffix = $outlet ? '_' . \Illuminate\Support\Str::slug($outlet->name) : '_semua-subdist';

        return Excel::download(
            new DistributorForecastExport($forecast, $outlet ? (string) $outlet->id : null),
            "forecast_{$distributor->distributor_sales_code}_{$year}{$suffix}.xlsx"
        );
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

    private function toIndex(int $year, $outletId = null): RedirectResponse
    {
        return redirect()->route('buyer.forecast.index', array_filter(['year' => $year, 'outlet' => $outletId]));
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

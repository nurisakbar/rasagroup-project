<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WarehousePricesExport;
use App\Http\Controllers\Controller;
use App\Imports\WarehousePricesImport;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductPrice;
use App\Support\Wib;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class WarehousePriceController extends Controller
{
    public const ALLOWED_ROLES = [User::ROLE_SUPER_ADMIN, 'ecommerce'];

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $warehouses = Warehouse::orderBy('name')->get(['id', 'slug', 'name', 'kode_hub', 'is_active']);
        $warehouse = $warehouses->firstWhere('id', $request->query('warehouse_id'));

        return view('admin.warehouse-prices.index', compact('warehouses', 'warehouse'));
    }

    public function datatable(Request $request, Warehouse $warehouse)
    {
        $this->authorizeAccess();
        $now = now();

        $current = fn (string $column) => WarehouseProductPrice::select($column)
            ->whereColumn('product_id', 'products.id')
            ->where('warehouse_id', $warehouse->id)
            ->effectiveAt($now)
            ->limit(1);

        $scheduled = WarehouseProductPrice::selectRaw('COUNT(*)')
            ->whereColumn('product_id', 'products.id')
            ->where('warehouse_id', $warehouse->id)
            ->where('effective_from', '>', $now);

        $query = Product::query()
            ->where('status', 'active')
            ->select('products.id', 'products.code', 'products.name', 'products.price')
            ->selectSub($current('price'), 'master_price')
            ->selectSub($current('effective_from'), 'master_from')
            ->selectSub($current('effective_to'), 'master_to')
            ->selectSub($scheduled, 'scheduled_count');

        if ($request->query('only_master') === '1') {
            $query->whereExists(fn ($q) => $q->from('warehouse_product_prices')
                ->whereColumn('product_id', 'products.id')
                ->where('warehouse_id', $warehouse->id));
        }

        return DataTables::of($query)
            ->editColumn('price', fn ($p) => $this->rupiah($p->price))
            ->addColumn('effective_price', function ($p) {
                if ($p->master_price === null) {
                    return $this->rupiah($p->price) . ' <span class="label label-default">Harga global</span>';
                }

                $period = 'sejak ' . Wib::format($p->master_from);
                if ($p->master_to) {
                    $period .= ' s.d. ' . Wib::format($p->master_to);
                }

                return '<strong>' . $this->rupiah($p->master_price) . '</strong><br><small class="text-muted">' . e($period) . '</small>';
            })
            ->addColumn('action', function ($p) {
                $scheduled = $p->scheduled_count > 0
                    ? ' <span class="label label-warning">' . (int) $p->scheduled_count . ' terjadwal</span>'
                    : '';

                return '<button type="button" class="btn btn-primary btn-xs btn-set-price" data-product-id="' . e($p->id) . '" data-product-name="' . e($p->code . ' - ' . $p->name) . '"><i class="fa fa-pencil"></i> Ubah</button> '
                    . '<button type="button" class="btn btn-default btn-xs btn-history" data-product-id="' . e($p->id) . '" data-product-name="' . e($p->code . ' - ' . $p->name) . '"><i class="fa fa-history"></i> Riwayat</button>'
                    . $scheduled;
            })
            ->filterColumn('code', fn ($q, $keyword) => $q->where('products.code', 'like', "%{$keyword}%"))
            ->filterColumn('name', fn ($q, $keyword) => $q->where('products.name', 'like', "%{$keyword}%"))
            ->rawColumns(['effective_price', 'action'])
            ->make(true);
    }

    public function store(Request $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        [$from, $to] = $this->period($validated['effective_from'] ?? null, $validated['effective_to'] ?? null);
        if ($to && $to->lte($from)) {
            return response()->json(['message' => 'Tanggal berakhir harus setelah tanggal mulai.', 'errors' => ['effective_to' => ['Tanggal berakhir harus setelah tanggal mulai.']]], 422);
        }

        WarehouseProductPrice::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $validated['product_id'],
            'price' => $validated['price'],
            'effective_from' => $from,
            'effective_to' => $to,
            'source' => WarehouseProductPrice::SOURCE_MANUAL,
            'note' => $validated['note'] ?? null,
            'created_by' => auth('admin')->id(),
        ]);

        return response()->json(['message' => 'Harga berhasil disimpan.']);
    }

    public function history(Warehouse $warehouse, Product $product): JsonResponse
    {
        $this->authorizeAccess();
        $now = now();
        $currentId = WarehouseProductPrice::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->effectiveAt($now)
            ->value('id');

        $rows = WarehouseProductPrice::with('creator:id,name')
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (WarehouseProductPrice $row) => [
                'id' => $row->id,
                'price' => $this->rupiah($row->price),
                'effective_from' => Wib::format($row->effective_from),
                'effective_to' => $row->effective_to ? Wib::format($row->effective_to) : null,
                'status' => $row->id === $currentId ? 'Berlaku' : ($row->isScheduled() ? 'Terjadwal' : 'Tidak berlaku'),
                'source' => $row->source,
                'note' => $row->note,
                'created_by' => $row->creator?->name,
                'created_at' => Wib::format($row->created_at),
                'can_cancel' => $row->isScheduled(),
            ]);

        return response()->json(['global_price' => $this->rupiah($product->price), 'rows' => $rows]);
    }

    /**
     * Hanya harga terjadwal yang bisa dibatalkan: harga yang sudah pernah berlaku adalah riwayat
     * dan mungkin sudah tercatat di penjualan.
     */
    public function destroy(Warehouse $warehouse, WarehouseProductPrice $price): JsonResponse
    {
        $this->authorizeAccess();
        abort_unless($price->warehouse_id === $warehouse->id, 404);

        if (! $price->isScheduled()) {
            return response()->json(['message' => 'Harga yang sudah berlaku tidak bisa dihapus. Buat harga baru untuk menggantinya.'], 422);
        }

        $price->delete();

        return response()->json(['message' => 'Jadwal harga dibatalkan.']);
    }

    public function export(Warehouse $warehouse)
    {
        $this->authorizeAccess();
        $filename = 'master-harga-' . str($warehouse->name)->slug() . '-' . now(Wib::TZ)->format('Ymd') . '.xlsx';

        return Excel::download(new WarehousePricesExport($warehouse), $filename);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeAccess();
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']]);

        $import = new WarehousePricesImport(auth('admin')->id());
        Excel::import($import, $request->file('file'));

        $back = redirect()->route('admin.warehouse-prices.index', ['warehouse_id' => $request->input('warehouse_id')]);

        if ($import->errors !== []) {
            return $back->with('error', 'Import dibatalkan, tidak ada harga yang disimpan. Perbaiki baris berikut lalu unggah ulang.')
                ->with('import_errors', array_slice($import->errors, 0, 50));
        }

        return $back->with('success', "Import berhasil: {$import->imported} harga disimpan.");
    }

    /**
     * Input tanggal dianggap WIB. Tanpa tanggal mulai, harga langsung berlaku.
     *
     * @return array{0: Carbon, 1: ?Carbon}
     */
    private function period(?string $from, ?string $to): array
    {
        $timezone = config('app.timezone');

        return [
            filled($from) ? Carbon::parse($from, Wib::TZ)->setTimezone($timezone) : now(),
            filled($to) ? Carbon::parse($to, Wib::TZ)->setTimezone($timezone) : null,
        ];
    }

    private function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(in_array(auth('admin')->user()?->role, self::ALLOWED_ROLES, true), 403);
    }
}

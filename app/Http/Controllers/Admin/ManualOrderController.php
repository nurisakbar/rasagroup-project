<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Expedition;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AdminManualOrderService;
use App\Services\WmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManualOrderController extends Controller
{
    public function __construct(private AdminManualOrderService $orders)
    {
    }

    public function create()
    {
        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'kode_hub']);

        $expeditions = Expedition::active()->orderBy('name')->get();

        return view('admin.orders.create', compact('warehouses', 'expeditions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'address_id' => ['required', 'exists:addresses,id'],
            'source_warehouse_id' => ['required', 'exists:warehouses,id'],
            'expedition_id' => ['required', 'exists:expeditions,id'],
            'expedition_service' => ['required', 'string', 'max:50'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'manual_transfer', 'term_of_payment'])],
            'mark_as_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'sales_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('users', 'sales_code')->where(fn ($q) => $q->where('role', User::ROLE_SALES)),
            ],
            'preferred_shipping_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.order_uom' => ['required', Rule::in(['base', 'large'])],
            'items.*.lot_serial' => ['required', 'string', 'max:100'],
        ]);

        $customer = User::with(['priceLevel', 'categoryDiscounts'])->findOrFail($validated['user_id']);
        $address = Address::with(['wilayah', 'village', 'district', 'regency', 'province'])
            ->where('id', $validated['address_id'])
            ->where('user_id', $customer->id)
            ->first();

        if (! $address) {
            return back()->withInput()->with('error', 'Alamat tidak valid atau bukan milik pelanggan yang dipilih.');
        }

        $warehouse = Warehouse::where('is_active', true)->findOrFail($validated['source_warehouse_id']);
        $expedition = Expedition::active()->findOrFail($validated['expedition_id']);
        $normalized = $this->orders->normalizeItems($validated['items']);

        $order = $this->orders->create($customer, $warehouse, $address, $expedition, $normalized, [
            'expedition_service' => $validated['expedition_service'],
            'shipping_cost' => (float) $validated['shipping_cost'],
            'payment_method' => $validated['payment_method'],
            'mark_as_paid' => $request->boolean('mark_as_paid'),
            'notes' => $validated['notes'] ?? null,
            'sales_code' => $validated['sales_code'] ?? null,
            'preferred_shipping_date' => $validated['preferred_shipping_date'] ?? null,
            'admin_name' => $request->user()->name,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Transaksi ' . $order->order_number . ' berhasil dibuat.');
    }

    public function searchCustomers(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->whereIn('role', [User::ROLE_BUYER, User::ROLE_DISTRIBUTOR, User::ROLE_OUTLET])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('qad_customer_code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email', 'phone', 'role', 'term_of_payment', 'qad_customer_code']);

        return response()->json([
            'results' => $users->map(function (User $user) {
                $meta = collect([
                    $user->role,
                    $user->email,
                    $user->qad_customer_code,
                ])->filter()->implode(' · ');

                return [
                    'id' => $user->id,
                    'text' => $user->name . ($meta ? " ({$meta})" : ''),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'term_of_payment' => (int) ($user->term_of_payment ?? 0),
                    'qad_customer_code' => $user->qad_customer_code,
                    'is_distributor' => $user->isDistributor(),
                ];
            }),
        ]);
    }

    public function searchSales(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $sales = User::query()
            ->where('role', User::ROLE_SALES)
            ->whereNotNull('sales_code')
            ->where('sales_code', '!=', '')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('sales_code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'email', 'sales_code']);

        return response()->json([
            'results' => $sales->map(fn (User $sale) => [
                'id' => $sale->sales_code,
                'text' => $sale->sales_code . ' — ' . $sale->name,
            ]),
        ]);
    }

    public function customerAddresses(User $user)
    {
        $addresses = Address::query()
            ->where('user_id', $user->id)
            ->with(['wilayah'])
            ->orderByDesc('is_default')
            ->get();

        return response()->json([
            'addresses' => $addresses->map(function (Address $address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label ?: 'Alamat',
                    'is_default' => (bool) $address->is_default,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'text' => trim(implode(', ', array_filter([
                        $address->label,
                        $address->recipient_name,
                        $address->address_detail,
                        $address->district_name,
                        $address->regency_name,
                    ]))),
                ];
            }),
            'term_of_payment' => (int) ($user->term_of_payment ?? 0),
            'is_distributor' => $user->isDistributor(),
        ]);
    }

    public function searchProducts(Request $request)
    {
        if (! $request->filled('user_id')) {
            return response()->json(['results' => []]);
        }

        $q = trim((string) $request->get('q', ''));
        $customer = User::find($request->get('user_id'));

        if (! $customer) {
            return response()->json(['results' => []]);
        }

        $products = Product::query()
            ->where('status', 'active')
            ->when($customer?->isDistributor(), function ($query) {
                $query->whereJsonContains('sync_sources', 'qad');
            }, function ($query) use ($customer) {
                if ($customer) {
                    $query->whereJsonContains('sync_sources', 'jubelio');
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $products->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'text' => ($product->code ? $product->code . ' — ' : '') . $product->name,
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'unit' => $product->unit,
                    'large_unit' => $product->large_unit,
                    'units_per_large' => $product->unitsPerLargeEffective(),
                    'has_large' => $product->hasDualUnitOrdering(),
                    'image' => $product->image_url,
                ];
            }),
        ]);
    }

    public function productBatches(Request $request, WmsService $wms)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $customer = User::findOrFail($validated['user_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $product = Product::findOrFail($validated['product_id']);
        $location = WmsService::locationCode($warehouse);

        if (! $location) {
            return response()->json([
                'batches' => [],
                'error' => 'Hub belum punya kode lokasi WMS/QAD.',
            ]);
        }

        $batches = $wms->localBatchesForItem(
            $location,
            (string) $product->code,
            $customer->shelfLifeMonths()
        );

        return response()->json([
            'batches' => array_values($batches),
        ]);
    }

    public function previewPricing(Request $request, AdminManualOrderService $service)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.order_uom' => ['required', Rule::in(['base', 'large'])],
        ]);

        $customer = User::with(['priceLevel', 'categoryDiscounts'])->findOrFail($validated['user_id']);
        $normalized = $service->normalizeItems($validated['items']);

        return response()->json($service->preview(
            $customer,
            $normalized,
            (float) ($validated['shipping_cost'] ?? 0)
        ));
    }
}

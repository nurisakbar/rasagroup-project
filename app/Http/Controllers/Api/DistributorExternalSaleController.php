<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\VerifyDistributorApiKey;
use App\Http\Requests\StoreDistributorExternalSaleRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DistributorApiClient;
use App\Models\DistributorExternalSale;
use App\Models\Product;
use App\Services\DistributorExternalSaleRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistributorExternalSaleController extends Controller
{
    public function store(StoreDistributorExternalSaleRequest $request, DistributorExternalSaleRecorder $recorder): JsonResponse
    {
        $result = $recorder->record($this->client($request), $request->validated(), $request->all());

        return response()->json([
            'success' => true,
            'message' => $result['created'] ? 'Penjualan berhasil dicatat.' : 'Penjualan berhasil diperbarui.',
            'data' => $this->present($result['sale']),
        ], $result['created'] ? 201 : 200);
    }

    public function show(Request $request, string $externalOrderNumber): JsonResponse
    {
        $sale = DistributorExternalSale::with('items')
            ->where('distributor_id', $this->client($request)->user_id)
            ->where('external_order_number', $externalOrderNumber)
            ->first();

        if (! $sale) {
            return response()->json(['success' => false, 'message' => 'Penjualan tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->present($sale)]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::where('status', 'active')->with(['brand:id,name,slug', 'category:id,name,slug'])->orderBy('name');

        if ($brand = trim((string) $request->query('brand'))) {
            $query->whereHas('brand', fn ($q) => $q->where('id', $brand)->orWhere('slug', $brand));
        }
        if ($category = trim((string) $request->query('category'))) {
            $query->whereHas('category', fn ($q) => $q->where('id', $category)->orWhere('slug', $category));
        }
        if ($search = trim((string) $request->query('search'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        $products = $query->get(['id', 'code', 'alternate_codes', 'name', 'unit', 'large_unit', 'units_per_large', 'brand_id', 'category_id'])
            ->map(fn (Product $product) => [
                'code' => $product->code,
                'alternate_codes' => $product->alternate_codes ?: [],
                'name' => $product->name,
                'unit' => $product->unit,
                'large_unit' => $product->large_unit,
                'units_per_large' => $product->units_per_large ? (int) $product->units_per_large : null,
                'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name, 'slug' => $product->brand->slug] : null,
                'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug] : null,
            ]);

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function categories(): JsonResponse
    {
        $data = Category::where('is_active', true)->orderBy('name')->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'products_count' => $c->products_count]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function brands(): JsonResponse
    {
        $data = Brand::where('is_active', true)->orderBy('name')->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->get(['id', 'name', 'slug'])
            ->map(fn (Brand $b) => ['id' => $b->id, 'name' => $b->name, 'slug' => $b->slug, 'products_count' => $b->products_count]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function client(Request $request): DistributorApiClient
    {
        return $request->attributes->get(VerifyDistributorApiKey::CLIENT_ATTRIBUTE);
    }

    private function present(DistributorExternalSale $sale): array
    {
        return [
            'external_order_number' => $sale->external_order_number,
            'status' => $sale->status,
            'sold_date' => $sale->sold_date->toDateString(),
            'total' => (float) $sale->total,
            'items_count' => $sale->items->count(),
            'unmapped_product_codes' => $sale->items->whereNull('product_id')->pluck('external_product_code')->unique()->values(),
        ];
    }
}

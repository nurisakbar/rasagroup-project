<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\VerifyDistributorApiKey;
use App\Http\Requests\StoreDistributorExternalSaleRequest;
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

    public function products(): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get(['code', 'alternate_codes', 'name', 'unit', 'large_unit'])
            ->map(fn (Product $product) => [
                'code' => $product->code,
                'alternate_codes' => $product->alternate_codes ?: [],
                'name' => $product->name,
                'unit' => $product->unit,
                'large_unit' => $product->large_unit,
            ]);

        return response()->json(['success' => true, 'data' => $products]);
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

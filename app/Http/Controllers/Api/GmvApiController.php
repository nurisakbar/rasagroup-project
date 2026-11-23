<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GmvAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GmvApiController extends Controller
{
    public function __construct(private readonly GmvAnalyticsService $service)
    {
    }

    /**
     * Ringkasan GMV (gross, diskon, ongkir, PPN, AOV, pertumbuhan) untuk satu periode.
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = $this->validated($request);

        return response()->json(['data' => $this->service->getSummaryMetrics($filters)]);
    }

    /**
     * GMV, revenue, dan jumlah order per hari dalam satu periode.
     */
    public function daily(Request $request): JsonResponse
    {
        $filters = $this->validated($request);
        $trend = $this->service->getDailyTrend($filters);
        $parsed = $this->service->parseFilters($filters);

        $start = $parsed['start_date']->copy();
        $rows = [];
        foreach ($trend['gmv'] as $i => $gmv) {
            $rows[] = [
                'date' => $start->copy()->addDays($i)->format('Y-m-d'),
                'gmv' => $gmv,
                'revenue' => $trend['revenue'][$i],
                'orders' => $trend['orders'][$i],
            ];
        }

        return response()->json(['data' => $rows]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:valid,paid,approved,all'],
            'warehouse_id' => ['nullable', 'string'],
            'channel' => ['nullable', 'in:all,sales,online,pos'],
            'sales_code' => ['nullable', 'string', 'max:50'],
        ]);
    }
}

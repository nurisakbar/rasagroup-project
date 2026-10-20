<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Webhook dari WMS: batch sales order diubah (mis. manual di QAD) → samakan allocated_batches di RasaConnect.
 *
 * Payload sama dengan format yang kita kirim ke WMS (POST /sales-orders):
 * {
 *   "so_number": "D2610901",
 *   "items": [
 *     {"item_code": "FDA010-VL01", "batches": [
 *        {"batch_number": "A02DVL 021026", "quantity": 5, "expired_at": "2027-10-02"}
 *     ]}
 *   ]
 * }
 *
 * - Item yang dikirim = daftar batch FINAL untuk item_code tsb (replace, bukan tambah).
 * - Item order yang tidak dikirim tidak diubah.
 * - Total qty batch per baris order harus sama dengan qty order (satuan basis).
 */
class WmsBatchUpdateController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $expectedKey = (string) config('services.wms.webhook_key');
        if ($expectedKey === '') {
            return $this->fail('WMS_WEBHOOK_KEY belum dikonfigurasi di server.', 500);
        }

        $providedKey = (string) ($request->header('x-api-key') ?? '');
        if ($providedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return $this->fail('Unauthorized: x-api-key tidak valid.', 401);
        }

        $validator = Validator::make($request->all(), [
            'so_number' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_code' => ['required', 'string', 'max:50'],
            'items.*.batches' => ['required', 'array', 'min:1'],
            'items.*.batches.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.batches.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.batches.*.expired_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Payload tidak valid.', 422, ['errors' => $validator->errors()]);
        }

        $soNumber = trim((string) $request->input('so_number'));

        $order = Order::with('items.product')
            ->where('order_number', $soNumber)
            ->orWhere('qid_sales_order_number', $soNumber)
            ->first();

        if (! $order) {
            return $this->fail("Order {$soNumber} tidak ditemukan.", 404);
        }

        // Kelompokkan batch dari payload per item_code (gabung jika item_code dikirim lebih dari sekali).
        $incomingByCode = [];
        foreach ($request->input('items') as $row) {
            $code = trim((string) $row['item_code']);
            foreach ($row['batches'] as $b) {
                $incomingByCode[$code][] = [
                    'lot_serial' => trim((string) $b['batch_number']),
                    'qty' => (int) round((float) $b['quantity']),
                    'expired' => ! empty($b['expired_at']) ? date('Y-m-d', strtotime((string) $b['expired_at'])) : null,
                    'item_code' => $code,
                ];
            }
        }

        // Cocokkan item_code ke baris order (lewat semua kode produk, termasuk kode alternatif).
        $codesPerItem = [];
        $unknownCodes = [];
        foreach (array_keys($incomingByCode) as $code) {
            $match = $order->items->first(function (OrderItem $item) use ($code) {
                return $item->product && in_array($code, $item->product->getAllCodes(), true);
            });
            if (! $match) {
                $unknownCodes[] = $code;
                continue;
            }
            $codesPerItem[$match->id][] = $code;
        }

        if ($unknownCodes !== []) {
            return $this->fail('Item tidak ada di order ini: '.implode(', ', $unknownCodes), 422);
        }

        // Bangun allocated_batches baru per baris + validasi total qty.
        $plans = [];
        $errors = [];
        foreach ($order->items as $item) {
            if (! isset($codesPerItem[$item->id])) {
                continue;
            }
            $codes = $codesPerItem[$item->id];
            $existing = is_array($item->allocated_batches) ? $item->allocated_batches : [];
            $defaultCode = $item->product?->code;

            // Pertahankan batch untuk item_code lain di baris yang sama; ganti batch untuk item_code yang dikirim.
            $kept = array_values(array_filter($existing, function ($b) use ($codes, $defaultCode) {
                return ! in_array($b['item_code'] ?? $defaultCode, $codes, true);
            }));

            $incoming = [];
            foreach ($codes as $code) {
                $incoming = array_merge($incoming, $incomingByCode[$code]);
            }
            $new = array_merge($kept, $this->mergeSameLot($incoming));

            $total = array_sum(array_map(fn ($b) => (int) ($b['qty'] ?? 0), $new));
            if ($total !== (int) $item->quantity) {
                $name = $item->product?->name ?? $defaultCode;
                $errors[] = "Total qty batch {$name} = {$total}, harus sama dengan qty order {$item->quantity}.";
                continue;
            }

            $plans[] = ['item' => $item, 'old' => $existing, 'new' => $new];
        }

        if ($errors !== []) {
            return $this->fail('Qty batch tidak sesuai.', 422, ['errors' => $errors]);
        }

        $changes = [];
        DB::transaction(function () use ($plans, &$changes) {
            foreach ($plans as $plan) {
                /** @var OrderItem $item */
                $item = OrderItem::whereKey($plan['item']->id)->lockForUpdate()->first();
                $item->allocated_batches = $plan['new'];
                $item->save();

                $changes[] = [
                    'item_code' => $plan['item']->product?->code,
                    'old_batches' => $plan['old'],
                    'new_batches' => $plan['new'],
                ];
            }
        });

        Log::info('WMS batch update applied', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'changes' => $changes,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batch berhasil diperbarui.',
            'data' => [
                'so_number' => $order->order_number,
                'updated_items' => array_map(fn ($c) => [
                    'item_code' => $c['item_code'],
                    'batches' => $c['new_batches'],
                ], $changes),
            ],
        ]);
    }

    /**
     * Gabungkan baris dengan lot yang sama (qty dijumlah).
     *
     * @param  array<int, array<string, mixed>>  $batches
     * @return array<int, array<string, mixed>>
     */
    private function mergeSameLot(array $batches): array
    {
        $merged = [];
        foreach ($batches as $b) {
            $key = $b['item_code'].'|'.$b['lot_serial'];
            if (isset($merged[$key])) {
                $merged[$key]['qty'] += $b['qty'];
                $merged[$key]['expired'] = $merged[$key]['expired'] ?? $b['expired'];
            } else {
                $merged[$key] = $b;
            }
        }

        return array_values($merged);
    }

    private function fail(string $message, int $status, array $extra = []): JsonResponse
    {
        if ($status >= 400 && $status !== 401) {
            Log::warning('WMS batch update rejected', ['status' => $status, 'message' => $message] + $extra);
        }

        return response()->json(['success' => false, 'message' => $message] + $extra, $status);
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Support\Facades\DB;

/**
 * Stok order subdist: dipotong dari stok gudang distributor (WarehouseStock) saat order dibuat,
 * dan dikembalikan bila order dibatalkan. Setiap perubahan dicatat di WarehouseStockHistory.
 */
class SubdistStockService
{
    /**
     * Potong stok satu produk. Harus dipanggil di dalam transaksi database.
     *
     * @throws \RuntimeException bila stok tidak cukup
     */
    public function deduct(Order $order, string $productId, int $quantity, string $productName = '', ?string $notes = null): void
    {
        $stock = WarehouseStock::where('warehouse_id', $order->source_warehouse_id)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        $before = (int) ($stock?->stock ?? 0);
        if (! $stock || $before < $quantity) {
            throw new \RuntimeException(sprintf(
                'Stok %s di distributor tidak mencukupi (dipesan %d, tersedia %d).',
                $productName !== '' ? $productName : 'produk',
                $quantity,
                $before
            ));
        }

        $stock->decrement('stock', $quantity);

        $this->record($order, $productId, $before, $before - $quantity, -$quantity, $notes ?? 'Order subdist ' . $order->order_number);
    }

    /**
     * Pastikan stok order ini sudah terpotong dari gudang asalnya. Aman dipanggil berulang:
     * hanya memotong selisih yang belum terpotong (dihitung dari riwayat stok order ini), jadi
     * order yang stoknya sudah terpotong saat dibuat tidak dipotong dua kali.
     *
     * @return int jumlah total unit yang baru dipotong pada pemanggilan ini
     *
     * @throws \RuntimeException bila stok gudang tidak mencukupi (tidak ada yang dipotong)
     */
    public function ensureDeducted(Order $order, string $reason = 'status pesanan'): int
    {
        return DB::transaction(function () use ($order, $reason) {
            $order->loadMissing('items.product');
            $total = 0;

            foreach ($order->items as $item) {
                $net = (int) WarehouseStockHistory::where('order_id', $order->id)
                    ->where('warehouse_id', $order->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->sum('quantity_added');

                $needed = (int) $item->quantity - max(0, -$net);
                if ($needed <= 0) {
                    continue;
                }

                $this->deduct(
                    $order,
                    (string) $item->product_id,
                    $needed,
                    (string) ($item->product?->display_name ?? $item->product?->name ?? ''),
                    "Pemotongan stok ({$reason}) order subdist {$order->order_number}"
                );
                $total += $needed;
            }

            return $total;
        });
    }

    /**
     * Kembalikan stok order yang dibatalkan. Aman dipanggil berulang: hanya mengembalikan selisih
     * yang masih terpotong (dihitung dari riwayat stok order ini).
     */
    public function restore(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                $net = (int) WarehouseStockHistory::where('order_id', $order->id)
                    ->where('warehouse_id', $order->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->sum('quantity_added');

                $toRestore = min((int) $item->quantity, -$net);
                if ($toRestore <= 0) {
                    continue;
                }

                $stock = WarehouseStock::where('warehouse_id', $order->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();
                if (! $stock) {
                    continue;
                }

                $before = (int) $stock->stock;
                $stock->increment('stock', $toRestore);
                $this->record($order, $item->product_id, $before, $before + $toRestore, $toRestore, 'Pembatalan order subdist ' . $order->order_number);
            }
        });
    }

    private function record(Order $order, string $productId, int $before, int $after, int $delta, string $notes): void
    {
        WarehouseStockHistory::create([
            'warehouse_id' => $order->source_warehouse_id,
            'product_id' => $productId,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'stock_before' => $before,
            'stock_after' => $after,
            'quantity_added' => $delta,
            'notes' => $notes,
        ]);
    }
}

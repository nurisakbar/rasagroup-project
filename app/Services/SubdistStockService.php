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
    public function deduct(Order $order, string $productId, int $quantity, string $productName = ''): void
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

        $this->record($order, $productId, $before, $before - $quantity, -$quantity, 'Order subdist ' . $order->order_number);
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

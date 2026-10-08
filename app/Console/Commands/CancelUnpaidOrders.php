<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\WarehouseStock;
use App\Support\ShopFulfillment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders that have not been paid after 30 minutes and restore stock.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeLimit = Carbon::now()->subMinutes(30);

        // Find orders created before $timeLimit and still pending payment
        $orders = Order::where('payment_status', 'pending')
                       ->where('order_status', 'pending')
                       ->where('created_at', '<', $timeLimit)
                       ->get();

        $count = 0;

        foreach ($orders as $order) {
            DB::beginTransaction();
            try {
                if (! ShopFulfillment::assumeStockReady()) {
                    // Return stock to warehouse
                    foreach ($order->items as $item) {
                        $stock = WarehouseStock::where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $item->product_id)
                            ->first();
                        if ($stock) {
                            $stock->increment('stock', $item->quantity);
                        }
                    }
                }

                // Update order status
                $order->update([
                    'order_status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);

                DB::commit();
                $count++;
                
                try {
                    $order->loadMissing('user');
                    if ($order->user && $order->user->email) {
                        \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderCancelledMail($order));
                    }
                } catch (\Exception $mailEx) {
                    Log::error("Failed to send cancellation email for {$order->order_number}: " . $mailEx->getMessage());
                }

                Log::info("Cancelled unpaid order {$order->order_number} automatically after 30 minutes.");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to cancel unpaid order {$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info("Cancelled {$count} unpaid orders.");
    }
}

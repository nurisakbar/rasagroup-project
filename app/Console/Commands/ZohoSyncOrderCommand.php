<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ZohoService;
use Illuminate\Console\Command;

class ZohoSyncOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync-order {order : Order ID or Order Number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync an Order to Zoho Books (Sales Order -> Invoice [Mark as Sent] -> Customer Payment)';

    /**
     * Execute the console command.
     */
    public function handle(ZohoService $zohoService): int
    {
        $orderIdentifier = $this->argument('order');
        $this->info("Looking up Order [{$orderIdentifier}]...");

        $order = Order::where('id', $orderIdentifier)
            ->orWhere('order_number', $orderIdentifier)
            ->first();

        if (!$order) {
            $this->error("Order [{$orderIdentifier}] not found.");
            return Command::FAILURE;
        }

        $this->info("Found Order {$order->order_number} (ID: {$order->id}). Total: Rp " . number_format($order->total_amount, 0, ',', '.'));
        $this->info("Current Zoho IDs:");
        $this->line(" - Sales Order ID: " . ($order->zoho_salesorder_id ?: '(none)'));
        $this->line(" - Invoice ID:     " . ($order->zoho_invoice_id ?: '(none)'));
        $this->line(" - Payment ID:     " . ($order->zoho_payment_id ?: '(none)'));

        $this->info("Starting Zoho Sync flow...");
        try {
            $zohoService->createOrderToPaymentFlow($order);
            $order->refresh();

            $this->info("Successfully synced Order {$order->order_number} to Zoho!");
            $this->line(" - Sales Order ID: " . $order->zoho_salesorder_id);
            $this->line(" - Invoice ID:     " . $order->zoho_invoice_id);
            $this->line(" - Payment ID:     " . $order->zoho_payment_id);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Sync Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

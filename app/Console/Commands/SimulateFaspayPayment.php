<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class SimulateFaspayPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faspay:simulate-payment {order_number? : The order number to simulate payment for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a Faspay payment webhook for a given order';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderNumber = $this->argument('order_number');

        if (!$orderNumber) {
            // Find the latest unpaid order
            $order = Order::where('payment_status', 'pending')
                ->where('order_status', 'pending')
                ->latest()
                ->first();

            if (!$order) {
                $this->error('No pending orders found to simulate.');
                return Command::FAILURE;
            }
            $orderNumber = $order->order_number;
            $this->info("No order number provided. Selected latest pending order: {$orderNumber}");
        } else {
            $order = Order::where('order_number', $orderNumber)->first();
            if (!$order) {
                $this->error("Order with number {$orderNumber} not found.");
                return Command::FAILURE;
            }
        }

        $this->info("Simulating Faspay webhook for order: {$orderNumber}");
        
        $url = 'http://127.0.0.1:8000/api/faspay/snap/payment';
        $this->info("Webhook URL: {$url}");

        $payload = [
            'virtualAccountNo' => $orderNumber,
            'latestTransactionStatus' => '00', // Success
            'paidAmount' => [
                'value' => number_format($order->total_amount, 2, '.', ''),
                'currency' => 'IDR'
            ],
            'type' => 'payment' // Ensure controller knows it's payment
        ];

        try {
            $response = Http::withHeaders([
                'CHANNEL-ID' => '77001',
                'X-SIGNATURE' => 'BYPASS_UAT_TESTING_2026',
            ])->post($url, $payload);

            if ($response->successful()) {
                $this->info('Webhook request sent successfully!');
                $this->line('Response: ' . $response->body());
                
                // Refresh order
                $order->refresh();
                $this->info("Order {$orderNumber} status updated:");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Payment Status', $order->payment_status],
                        ['Order Status', $order->order_status]
                    ]
                );

                if ($order->payment_status === 'paid') {
                    $this->info("✅ Simulation Success: Order is paid.");
                } else {
                    $this->error("❌ Simulation Failed: Order is not paid.");
                }

                return Command::SUCCESS;
            } else {
                $this->error('Webhook request failed with status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error sending request: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Jobs\SyncCustomerToQad;
use App\Jobs\SyncOrderToQad;

class FixBrokenQadCustomer extends Command
{
    protected $signature = 'qad:fix-broken {email}';
    protected $description = 'Remove broken QAD customer code and recreate it';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $oldCode = $user->qad_customer_code;
        $this->info("User currently has QAD Code: $oldCode. Setting to null.");

        $user->qad_customer_code = null;
        $user->save();

        // Get address snapshot
        $address = clone $user->addresses()->orderByDesc('is_default')->first();
        $snapshot = [];
        if ($address) {
            $snapshot = [
                'city' => $address->regency->name ?? '',
                'street1' => $address->address_detail ?? '',
                'postal_code' => $address->postal_code ?? '',
            ];
        }

        $this->info("Dispatching SyncCustomerToQad synchronously...");
        $job = new SyncCustomerToQad($user, $snapshot);
        $job->handle(app(\App\Services\QadService::class));

        $user->refresh();
        $this->info("New QAD Code: " . $user->qad_customer_code);

        // Now dispatch SyncOrderToQad for the latest order of this user
        $order = Order::where('user_id', $user->id)->latest()->first();
        if ($order) {
            $this->info("Dispatching SyncOrderToQad synchronously for Order: {$order->order_number}...");
            $job = new SyncOrderToQad($order);
            $job->handle(app(\App\Services\QadService::class));
            $this->info("Done syncing order.");
        } else {
            $this->warn("No orders found for user.");
        }
    }
}

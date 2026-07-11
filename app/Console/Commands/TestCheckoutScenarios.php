<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Address;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Expedition;
use Illuminate\Http\Request;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TestCheckoutScenarios extends Command
{
    protected $signature = 'test:checkout-scenarios';
    protected $description = 'Simulate checkout scenarios for Jubelio Hubs, Distributors, and QAD restock';

    public function handle()
    {
        Config::set('shop.assume_stock_ready', false);
        
        $this->info("=== STARTING CHECKOUT SIMULATION ===");

        // 1. Setup User
        $email = 'nuris.akbar@gmail.com';
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::factory()->create(['email' => $email, 'password' => bcrypt('password')]);
        }
        $this->info("1. User {$email} found.");

        // 2. Find Product and Expedition
        $product = Product::where('code', 'FDA010-CM01')->first();
        if (!$product) {
            $this->error("Product FDA010-CM01 not found");
            return;
        }

        $expedition = Expedition::first();
        if (!$expedition) {
            $this->error("No active expedition found");
            return;
        }
        $expService = 'REGPACK';

        // 3. Setup Jubelio Hub
        $hub = Warehouse::where('kode_hub', 'WHMPRG001')->first();
        if (!$hub) {
            $this->error("Jubelio Hub WHMPRG001 not found");
            return;
        }
        $this->info("2. Hub identified: {$hub->name}");

        // 4. Setup Distributor
        $distributor = User::firstOrCreate(
            ['email' => 'distributor_sim@example.com'],
            ['name' => 'Distributor Sim', 'password' => bcrypt('password'), 'role' => User::ROLE_DISTRIBUTOR]
        );
        $distWarehouse = Warehouse::firstOrCreate(
            ['kode_hub' => 'SIM_DIST'],
            ['name' => 'Gudang Distributor Sim', 'sync_sources' => [], 'district_id' => 110501, 'is_active' => true]
        );
        $distributor->warehouse_id = $distWarehouse->id;
        $distributor->save();

        WarehouseStock::updateOrCreate(
            ['warehouse_id' => $distWarehouse->id, 'product_id' => $product->id],
            ['stock' => 5]
        );
        $this->info("3. Distributor Warehouse created with 5 stock.");

        // 5. Addresses for User
        $hubAddress = Address::firstOrCreate(
            ['user_id' => $user->id, 'district_id' => $hub->district_id ?? 110502],
            ['recipient_name' => 'Nuris Hub', 'phone' => '08123456789', 'address_detail' => 'Test Hub Address']
        );
        $distAddress = Address::firstOrCreate(
            ['user_id' => $user->id, 'district_id' => $distWarehouse->district_id],
            ['recipient_name' => 'Nuris Dist', 'phone' => '08123456789', 'address_detail' => 'Test Dist Address']
        );
        
        $distributorAddress = Address::firstOrCreate(
            ['user_id' => $distributor->id, 'district_id' => $distWarehouse->district_id],
            ['recipient_name' => 'Distributor HQ', 'phone' => '08123456789', 'address_detail' => 'Alamat Distributor']
        );
        $this->info("4. Buyer addresses setup.");

        Auth::login($user);

        // --- SCENARIO 1: HUB SUCCESS ---
        $this->info("\n--- SCENARIO 1: HUB CHECKOUT (SUCCESS) ---");
        $this->simulateCheckout($user, $hubAddress, $hub, $product, 1, $expedition, $expService, false);

        // --- SCENARIO 2: HUB FAIL (OUT OF STOCK IN JUBELIO) ---
        $this->info("\n--- SCENARIO 2: HUB CHECKOUT (FAIL JUBELIO STOCK) ---");
        $this->simulateCheckout($user, $hubAddress, $hub, $product, 999, $expedition, $expService, true);

        // --- SCENARIO 3: DISTRIBUTOR SUCCESS ---
        $this->info("\n--- SCENARIO 3: DISTRIBUTOR CHECKOUT (SUCCESS) ---");
        $this->simulateCheckout($user, $distAddress, $distWarehouse, $product, 2, $expedition, $expService, false);

        // --- SCENARIO 4: DISTRIBUTOR FAIL (OUT OF STOCK LOCALLY) ---
        $this->info("\n--- SCENARIO 4: DISTRIBUTOR CHECKOUT (FAIL LOCAL STOCK) ---");
        $this->simulateCheckout($user, $distAddress, $distWarehouse, $product, 999, $expedition, $expService, true);

        // --- SCENARIO 5: DISTRIBUTOR RESTOCK (TO QAD) ---
        $this->info("\n--- SCENARIO 5: DISTRIBUTOR RESTOCK (TO QAD) ---");
        Auth::login($distributor);
        $this->simulateDistributorRestock($distributor, $distributorAddress, $hub, $product, 10, $expedition, $expService);

        $this->info("\n=== SIMULATION COMPLETED ===");
    }

    private function simulateCheckout($user, $address, $warehouse, $product, $qty, $expedition, $expService, $expectFail)
    {
        DB::table('carts')->where('user_id', $user->id)->delete();
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $qty,
            'cart_type' => 'regular'
        ]);

        $request = Request::create('/checkout', 'POST', [
            'address_id' => $address->id,
            'expedition_id' => $expedition->id,
            'expedition_service' => $expService,
            'payment_method' => 'manual_transfer'
        ]);

        session()->forget('error');
        session()->forget('success');
        
        $controller = app(CheckoutController::class);
        $response = $controller->store($request);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $session = $response->getSession();
            if ($session && $session->has('error')) {
                $errorMsg = $session->get('error');
                if ($expectFail) {
                    $this->info("✓ SUCCESS: Checkout blocked as expected. Reason: \n" . $errorMsg);
                } else {
                    $this->error("✗ FAILED: Checkout was blocked unexpectedly. Reason: \n" . $errorMsg);
                }
            } else if ($session && $session->has('success')) {
                if (!$expectFail) {
                    $this->info("✓ SUCCESS: Checkout passed. Order created!");
                } else {
                    $this->error("✗ FAILED: Checkout passed but was expected to fail.");
                }
            } else {
                $target = $response->getTargetUrl();
                if (!$expectFail && str_contains($target, 'checkout/success')) {
                    $this->info("✓ SUCCESS: Checkout passed. Redirected to success page.");
                } else {
                    $this->info("Result: Redirected to " . $target);
                }
            }
        } else {
            $this->info("Response is not redirect.");
        }
    }

    private function simulateDistributorRestock($user, $address, $hub, $product, $qty, $expedition, $expService)
    {
        DB::table('carts')->where('user_id', $user->id)->where('cart_type', 'distributor')->delete();
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'warehouse_id' => $hub->id,
            'quantity' => $qty,
            'cart_type' => 'distributor'
        ]);

        $request = Request::create('/distributor/checkout', 'POST', [
            'address_id' => $address->id,
            'expedition_id' => $expedition->id,
            'expedition_service' => $expService,
            'payment_method' => 'manual_transfer'
        ]);

        session()->forget('error');
        session()->forget('success');
        
        $controller = app(\App\Http\Controllers\Distributor\OrderController::class);
        $response = $controller->store($request);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $session = $response->getSession();
            if ($session && $session->has('error')) {
                $this->error("✗ FAILED: Restock blocked. Reason: \n" . $session->get('error'));
            } else {
                $target = $response->getTargetUrl();
                if (str_contains($target, 'distributor/orders') || str_contains($target, 'success')) {
                    $this->info("✓ SUCCESS: Restock order created! It will be synced to QAD upon payment.");
                    
                    // Verify QAD Sync is configured for this order type
                    $order = \App\Models\Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                    if ($order && $order->order_type === \App\Models\Order::TYPE_DISTRIBUTOR) {
                        $this->info("✓ Order Type is correct: TYPE_DISTRIBUTOR");
                        if ($order->shouldSyncToQad()) {
                            $this->info("✓ shouldSyncToQad() is true. The job SyncOrderToQad will run when paid.");
                        } else {
                            $this->error("✗ shouldSyncToQad() is false!");
                        }
                    }
                } else {
                    $this->info("Result: Redirected to " . $target);
                }
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Address;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Expedition;
use Illuminate\Http\Request;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Jobs\CreateShipmentBooking;
use App\Services\EkspedisiKuService;
use App\Services\RajaOngkirService;
use Mockery;

class TestDistributorShippingScenarios extends Command
{
    protected $signature = 'test:distributor-shipping';
    protected $description = 'Simulate buyer checkout to distributor for all expeditions, trigger booking & pickup';

    public function handle()
    {
        // Mock EkspedisiKuService ONLY for calculateCost
        $ekspedisiku = Mockery::mock(EkspedisiKuService::class)->makePartial();
        $ekspedisiku->__construct();
        $ekspedisiku->shouldReceive('calculateCost')->andReturnUsing(function($origin, $dest, $weight, $courier, $opts) {
            $real = new EkspedisiKuService();
            if ($courier === 'lalamove') {
                return ['data' => [['code' => 'lalamove', 'service' => 'MOTORCYCLE', 'cost' => 10000]]];
            }
            return $real->calculateCost($origin, $dest, $weight, $courier, $opts);
        });
        app()->instance(EkspedisiKuService::class, $ekspedisiku);
        
        $ro = Mockery::mock(RajaOngkirService::class)->makePartial();
        $ro->__construct();
        $ro->shouldReceive('calculateCost')->andReturnUsing(function($origin, $dest, $weight, $courier) {
            if ($courier === 'sicepat') {
                return ['data' => [['code' => 'sicepat', 'service' => 'BEST', 'cost' => 10000]]];
            }
            if ($courier === 'anteraja') {
                return ['data' => [['code' => 'anteraja', 'service' => 'REG', 'cost' => 10000]]];
            }
            if ($courier === 'pos') {
                return ['data' => [['code' => 'pos', 'service' => 'REG', 'cost' => 10000]]];
            }
            if ($courier === 'jnt') {
                return ['data' => [['code' => 'jnt', 'service' => 'EZ', 'cost' => 10000]]];
            }
            return (new RajaOngkirService())->calculateCost($origin, $dest, $weight, $courier);
        });
        app()->instance(RajaOngkirService::class, $ro);

        Config::set('shop.assume_stock_ready', false);
        Config::set('shop.auto_hub_by_address', false);
        
        $this->info("=== STARTING DISTRIBUTOR SHIPPING SIMULATION ===");

        $email = 'nuris.akbar@gmail.com';
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found");
            return;
        }

        $product = Product::where('code', 'FMF020-CT12')->first();
        if (!$product) {
            $this->error("Product FMF020-CT12 not found");
            return;
        }

        $distWarehouse = Warehouse::where('kode_hub', 'SIM_DIST')->first();
        if (!$distWarehouse) {
            $this->error("Distributor Warehouse SIM_DIST not found");
            return;
        }
        
        WarehouseStock::updateOrCreate(
            ['warehouse_id' => $distWarehouse->id, 'product_id' => $product->id],
            ['stock' => 1000]
        );

        $distWarehouse->update(['latitude' => '-6.175110', 'longitude' => '106.865039']);
        $distAddress = Address::firstOrCreate(
            ['user_id' => $user->id, 'district_id' => 317301],
            ['recipient_name' => 'Nuris Dist', 'phone' => '08123456789', 'address_detail' => 'Test Dist Address', 'postal_code' => '10110', 'latitude' => '-6.175110', 'longitude' => '106.865039']
        );

        Auth::login($user);

        $expeditions = Expedition::where('is_active', true)->whereIn('code', ['lion_parcel', 'jne', 'sicepat', 'lalamove'])->get();
        if ($expeditions->isEmpty()) {
            $this->error("No active expeditions found.");
            return;
        }

        foreach ($expeditions as $exp) {
            $this->info("\n=======================================================");
            $this->info("TESTING EXPEDITION: {$exp->name} ({$exp->code})");
            $this->info("=======================================================");
            
            $cost = null;
            if (in_array($exp->code, ['lion_parcel', 'lalamove', 'jne'])) {
                $cost = $ekspedisiku->calculateCost(
                    $distWarehouse->district_id, 
                    $distAddress->district_id, 
                    1000, 
                    $exp->code, 
                    ['warehouse' => $distWarehouse, 'address' => $distAddress]
                );
            } else {
                $cost = $ro->calculateCost(
                    $distWarehouse->district_id, 
                    $distAddress->district_id, 
                    1000, 
                    $exp->code
                );
            }
            
            $serviceCode = null;
            if ($cost && is_array($cost) && isset($cost['data']) && !empty($cost['data'])) {
                $serviceCode = $cost['data'][0]['service'];
            }
            
            $serviceCode = $exp->code === 'lion_parcel' ? 'REGPACK' : ($exp->code === 'jne' ? 'CTC15' : $serviceCode);

            if (!$serviceCode) {
                $this->error("✗ Could not find a valid shipping service for {$exp->name}. Skipping.");
                continue;
            }
            
            $this->info("Found Service Code: {$serviceCode}");
            
            $lastOrderId = Order::max('id');
            $order = $this->simulateCheckout($user, $distAddress, $distWarehouse, $product, 1, $exp, $serviceCode);
            
            if (!$order || $order->id === $lastOrderId) {
                $this->error("✗ Failed to create order for {$exp->name}. Error: " . session('error'));
                continue;
            }
            
            $this->info("✓ Order created: {$order->order_number}");
            
            $order->update(['payment_status' => 'paid', 'order_status' => 'processing']);
            $this->info("✓ Order marked as paid");
            
            $this->info("→ Creating Shipment Booking...");
            $job = new CreateShipmentBooking($order);
            $job->handle($ekspedisiku);
            
            $order->refresh();
            
            if ($order->ekspedisiku_booking_status === 'success') {
                $this->info("✓ Booking SUCCESS!");
                $this->info("  - Booking Ref: {$order->ekspedisiku_booking_reference}");
                $this->info("  - Shipment ID: {$order->ekspedisiku_shipment_id}");
                $this->info("  - Tracking #: {$order->tracking_number}");
                
                if ($order->ekspedisiku_shipment_id) {
                    $this->info("→ Requesting Pickup...");
                    $startAt = now()->addHour()->format('Y-m-d H:i:s');
                    $endAt = now()->addHours(4)->format('Y-m-d H:i:s');
                    
                    $pickupRes = $ekspedisiku->requestPickupDebug([$order->ekspedisiku_shipment_id], $startAt, $endAt, true);
                    
                    if (is_array($pickupRes) && ($pickupRes['success'] ?? false) || !isset($pickupRes['error'])) {
                        $this->info("✓ Pickup requested successfully!");
                        $this->line("  Pickup response: " . json_encode($pickupRes['response'] ?? $pickupRes));
                    } else {
                        $this->error("✗ Pickup request failed: " . json_encode($pickupRes['error'] ?? $pickupRes));
                    }
                } else {
                    $this->error("✗ No shipment ID available for pickup request.");
                }
                
            } else {
                $this->error("✗ Booking FAILED!");
                $this->error("  Reason: {$order->ekspedisiku_booking_last_error}");
            }
        }
        
        $this->info("\n=== ALL SCENARIOS COMPLETED ===");
    }

    private function simulateCheckout($user, $address, $warehouse, $product, $qty, $expedition, $expService)
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
        
        return Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    }
}

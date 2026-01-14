<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\District;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointWithdrawal;
use App\Models\Product;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up old demo data...');
        $this->cleanupOldData();

        $this->command->info('Creating demo data...');

        // Get existing data
        $provinces = Province::all();
        $regencies = Regency::all();
        $products = Product::where('status', 'active')->get();
        $expeditions = Expedition::where('is_active', true)->get();

        if ($provinces->isEmpty() || $regencies->isEmpty()) {
            $this->command->error('Please run IndonesiaRegionsSeeder first!');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('No products found! Please import products first.');
            return;
        }

        if ($expeditions->isEmpty()) {
            $this->command->error('No expeditions found! Please create expeditions first.');
            return;
        }

        // 1. Create 15 Hubs (Warehouses) with correct locations and warehouse users
        $this->command->info('Creating 15 hubs (warehouses)...');
        $hubs = [];
        $hubConfigs = [
            ['name' => 'Hub Jakarta Pusat', 'regency_id' => '3171'],
            ['name' => 'Hub Jakarta Selatan', 'regency_id' => '3174'],
            ['name' => 'Hub Jakarta Barat', 'regency_id' => '3173'],
            ['name' => 'Hub Bandung', 'regency_id' => '3204'],
            ['name' => 'Hub Surabaya', 'regency_id' => '3578'],
            ['name' => 'Hub Yogyakarta', 'regency_id' => '3471'],
            ['name' => 'Hub Semarang', 'regency_id' => '3322'],
            ['name' => 'Hub Medan', 'regency_id' => '1271'],
            ['name' => 'Hub Makassar', 'regency_id' => '7371'],
            ['name' => 'Hub Palembang', 'regency_id' => '1671'],
            ['name' => 'Hub Denpasar', 'regency_id' => '5171'],
            ['name' => 'Hub Malang', 'regency_id' => '3573'], // KOTA MALANG
            ['name' => 'Hub Solo', 'regency_id' => '3372'], // KOTA SURAKARTA
            ['name' => 'Hub Bekasi', 'regency_id' => '3216'],
            ['name' => 'Hub Tangerang', 'regency_id' => '3603'],
        ];

        $hubUsers = [];
        foreach ($hubConfigs as $index => $config) {
            $regency = Regency::find($config['regency_id']);
            if (!$regency) {
                $this->command->warn("  ⚠ Regency {$config['regency_id']} not found, using random location");
                $regency = $regencies->random();
            }

            $hub = Warehouse::create([
                'id' => (string) Str::uuid(),
                'name' => $config['name'],
                'address' => "Jl. Raya " . $regency->name . " No. " . ($index + 1) . ", " . $regency->province->name,
                'phone' => '08' . rand(100000000, 999999999),
                'description' => 'Hub utama untuk distribusi produk',
                'province_id' => $regency->province_id,
                'regency_id' => $regency->id,
                'is_active' => true,
            ]);

            // Create warehouse user for login
            $hubUser = User::create([
                'id' => (string) Str::uuid(),
                'name' => 'Admin ' . $config['name'],
                'email' => strtolower(str_replace(' ', '', str_replace('Hub ', 'hub', $config['name']))) . '@demo.com',
                'password' => Hash::make('password'),
                'phone' => '08' . rand(100000000, 999999999),
                'role' => User::ROLE_WAREHOUSE,
                'warehouse_id' => $hub->id,
            ]);

            $hubs[] = $hub;
            $hubUsers[] = $hubUser;
        }
        $this->command->info('✓ Created ' . count($hubs) . ' hubs with warehouse users');
        $this->command->info('  → Loginable hub users: hubjakartapusat@demo.com, hubjakartaselatan@demo.com, etc. (password: password)');

        // Create warehouse stocks for each hub
        $this->command->info('Creating warehouse stocks...');
        $stocksCreated = 0;
        foreach ($hubs as $hub) {
            // Each hub gets 20-50 random products with stock
            $hubProducts = $products->random(rand(20, min(50, $products->count())));
            foreach ($hubProducts as $product) {
                WarehouseStock::create([
                    'id' => (string) Str::uuid(),
                    'warehouse_id' => $hub->id,
                    'product_id' => $product->id,
                    'stock' => rand(10, 500), // Random stock between 10-500
                ]);
                $stocksCreated++;
            }
        }
        $this->command->info('✓ Created ' . $stocksCreated . ' warehouse stocks');

        // 2. Create 40 Distributors with their own warehouses (first 5 are loginable with clear emails)
        $this->command->info('Creating 40 distributors with warehouses...');
        
        $distributors = [];
        $distributorNames = [
            'PT Distributor Sejahtera', 'CV Makmur Jaya', 'UD Berkah Abadi',
            'PT Sumber Rezeki', 'CV Jaya Makmur', 'PT Distributor Nusantara',
            'UD Sentosa Jaya', 'CV Mandiri Sejahtera', 'PT Distributor Prima',
            'UD Makmur Sentosa', 'CV Sejahtera Abadi', 'PT Distributor Jaya',
            'UD Berkah Makmur', 'CV Nusantara Jaya', 'PT Distributor Sentosa',
            'UD Prima Sejahtera', 'CV Abadi Makmur', 'PT Distributor Mandiri',
            'UD Jaya Sentosa', 'CV Sejahtera Nusantara', 'PT Distributor Berkah',
            'UD Makmur Prima', 'CV Jaya Abadi', 'PT Distributor Sentosa Jaya',
            'UD Sejahtera Mandiri', 'CV Nusantara Makmur', 'PT Distributor Prima Jaya',
            'UD Berkah Sentosa', 'CV Abadi Sejahtera', 'PT Distributor Makmur Jaya',
            'UD Jaya Prima', 'CV Sentosa Abadi', 'PT Distributor Nusantara Sejahtera',
            'UD Mandiri Berkah', 'CV Prima Makmur', 'PT Distributor Jaya Sentosa',
            'UD Sejahtera Abadi', 'CV Berkah Prima', 'PT Distributor Makmur Sentosa',
            'UD Nusantara Jaya', 'CV Abadi Mandiri'
        ];

        // Loginable distributor emails (first 5)
        $loginableEmails = [
            'distributor1@demo.com',
            'distributor2@demo.com',
            'distributor3@demo.com',
            'distributor4@demo.com',
            'distributor5@demo.com',
        ];

        for ($i = 0; $i < 40; $i++) {
            $province = $provinces->random();
            $regency = $regencies->where('province_id', $province->id)->random();

            $email = $i < 5 
                ? $loginableEmails[$i] 
                : 'distributor' . ($i + 1) . '@demo.com';

            // Create warehouse for distributor
            $distributorWarehouse = Warehouse::create([
                'id' => (string) Str::uuid(),
                'name' => 'Hub ' . ($distributorNames[$i] ?? 'Distributor ' . ($i + 1)),
                'address' => "Jl. Raya " . $regency->name . " No. " . ($i + 1) . ", " . $province->name,
                'phone' => '08' . rand(100000000, 999999999),
                'description' => 'Warehouse distributor',
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'is_active' => true,
            ]);

            // Create distributor user with warehouse
            $distributor = User::create([
                'id' => (string) Str::uuid(),
                'name' => $distributorNames[$i] ?? 'Distributor ' . ($i + 1),
                'email' => $email,
                'password' => Hash::make('password'),
                'phone' => '08' . rand(100000000, 999999999),
                'role' => User::ROLE_DISTRIBUTOR,
                'warehouse_id' => $distributorWarehouse->id,
                'no_ktp' => rand(1000000000000000, 9999999999999999),
                'no_npwp' => rand(100000000000000, 999999999999999),
                'distributor_status' => 'approved',
                'distributor_province_id' => $province->id,
                'distributor_regency_id' => $regency->id,
                'distributor_address' => "Jl. Raya " . $regency->name . " No. " . ($i + 1) . ", " . $province->name,
                'points' => rand(0, 50000),
            ]);

            // Create warehouse stocks for distributor warehouse
            $distributorProducts = $products->random(rand(15, min(40, $products->count())));
            foreach ($distributorProducts as $product) {
                WarehouseStock::create([
                    'id' => (string) Str::uuid(),
                    'warehouse_id' => $distributorWarehouse->id,
                    'product_id' => $product->id,
                    'stock' => rand(5, 300), // Random stock between 5-300
                ]);
            }

            $distributors[] = $distributor;
        }
        $this->command->info('✓ Created ' . count($distributors) . ' distributors with warehouses and stocks');
        $this->command->info('  → Loginable: distributor1@demo.com to distributor5@demo.com (password: password)');

        // 3. Create 50 Driippreneurs
        $this->command->info('Creating 50 driippreneurs...');
        $driippreneurs = [];
        $driippreneurNames = [
            'Budi Santoso', 'Siti Nurhaliza', 'Ahmad Fauzi', 'Dewi Sartika', 'Rudi Hartono',
            'Maya Sari', 'Indra Gunawan', 'Ratna Dewi', 'Agus Setiawan', 'Lina Marlina',
            'Eko Prasetyo', 'Rina Wati', 'Bambang Sudrajat', 'Sari Indah', 'Dedi Kurniawan',
            'Nurul Hidayati', 'Joko Widodo', 'Fitri Handayani', 'Ari Wibowo', 'Yuni Astuti',
            'Hendra Kurniawan', 'Dwi Lestari', 'Fajar Nugroho', 'Sinta Dewi', 'Rizki Pratama',
            'Mira Sari', 'Adi Saputra', 'Nina Kartika', 'Bayu Ramadhan', 'Dina Oktaviani',
            'Feri Setiawan', 'Rina Puspita', 'Guntur Wijaya', 'Sari Mulyani', 'Hadi Kurniawan',
            'Lia Anggraini', 'Iwan Setiawan', 'Dewi Lestari', 'Aris Munandar', 'Siti Aisyah',
            'Bambang Suryadi', 'Rina Kartika', 'Eko Yulianto', 'Dina Sari', 'Fajar Maulana',
            'Mira Handayani', 'Ahmad Rizki', 'Sari Indrawati', 'Dedi Prasetyo', 'Nurul Fitri'
        ];

        for ($i = 0; $i < 50; $i++) {
            $province = $provinces->random();
            $regency = $regencies->where('province_id', $province->id)->random();

            $driippreneur = User::create([
                'id' => (string) Str::uuid(),
                'name' => $driippreneurNames[$i] ?? 'Driippreneur ' . ($i + 1),
                'email' => 'driippreneur' . ($i + 1) . '@demo.com',
                'password' => Hash::make('password'),
                'phone' => '08' . rand(100000000, 999999999),
                'role' => User::ROLE_DRIIPPRENEUR,
                'driippreneur_status' => 'approved',
                'driippreneur_province_id' => $province->id,
                'driippreneur_regency_id' => $regency->id,
                'driippreneur_address' => "Jl. " . $regency->name . " No. " . ($i + 1) . ", " . $province->name,
                'driippreneur_applied_at' => now()->subDays(rand(1, 90)),
                'points' => rand(0, 100000),
            ]);

            $driippreneurs[] = $driippreneur;
        }
        $this->command->info('✓ Created ' . count($driippreneurs) . ' driippreneurs');

        // 4. Create 150 orders per distributor (mix of online and POS) within this month
        $this->command->info('Creating 150 orders per distributor (within this month)...');
        $orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $paymentMethods = ['cash', 'transfer_bank', 'qris', 'kartu_debit', 'kartu_kredit', 'manual_transfer'];
        
        $totalOrdersCreated = 0;
        $orderNumberCounter = 1;
        $posOrderNumberCounter = 1;
        
        // Get current month start and end
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;
        
        foreach ($distributors as $distributorIndex => $distributor) {
            $distributorWarehouse = Warehouse::where('id', $distributor->warehouse_id)->first();
            if (!$distributorWarehouse) {
                $this->command->warn("  ⚠ Distributor {$distributor->name} has no warehouse, skipping orders...");
                continue;
            }
            
            $this->command->info("  Creating 150 orders for {$distributor->name}...");
            $distributorOrdersCreated = 0;
            
            // Create 150 orders with different dates within this month
            for ($i = 0; $i < 150; $i++) {
                // Random day within this month (0 to daysInMonth-1)
                $randomDay = rand(0, $daysInMonth - 1);
                $orderCreatedAt = $monthStart->copy()->addDays($randomDay);
                
                // Random time within the day
                $orderCreatedAt->setTime(rand(8, 20), rand(0, 59), rand(0, 59));
                
                // Mix of online (regular) and POS orders (60% online, 40% POS)
                $orderType = rand(1, 100) <= 60 ? Order::TYPE_REGULAR : Order::TYPE_POS;
                
                // For POS orders, payment is usually paid and status is completed
                if ($orderType === Order::TYPE_POS) {
                    $paymentStatus = 'paid';
                    $orderStatus = rand(1, 100) <= 80 ? 'completed' : (rand(1, 100) <= 50 ? 'delivered' : 'shipped');
                    $posPaymentMethods = ['cash', 'transfer_bank', 'qris', 'kartu_debit'];
                    $paymentMethod = $posPaymentMethods[array_rand($posPaymentMethods)];
                    $shippingCost = 0; // POS orders have no shipping
                } else {
                    // Regular online orders
                    $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                    $orderStatus = $orderStatuses[array_rand($orderStatuses)];
                    $paymentMethod = 'manual_transfer';
                    $shippingCost = rand(10000, 50000);
                }
                
                // Generate order number based on type
                $orderDate = $orderCreatedAt->format('Ymd');
                if ($orderType === Order::TYPE_POS) {
                    $orderNumber = 'POS-' . $orderDate . '-' . str_pad($posOrderNumberCounter++, 4, '0', STR_PAD_LEFT);
                } else {
                    $orderNumber = 'ORD-' . $orderDate . '-' . str_pad($orderNumberCounter++, 4, '0', STR_PAD_LEFT);
                }
                
                // Ensure uniqueness
                while (Order::where('order_number', $orderNumber)->exists()) {
                    if ($orderType === Order::TYPE_POS) {
                        $posOrderNumberCounter++;
                        $orderNumber = 'POS-' . $orderDate . '-' . str_pad($posOrderNumberCounter, 4, '0', STR_PAD_LEFT);
                    } else {
                        $orderNumberCounter++;
                        $orderNumber = 'ORD-' . $orderDate . '-' . str_pad($orderNumberCounter, 4, '0', STR_PAD_LEFT);
                    }
                }
                
                // For POS orders, use distributor warehouse as source
                // For regular orders, use random hub
                $sourceWarehouseId = $orderType === Order::TYPE_POS 
                    ? $distributorWarehouse->id 
                    : $hubs[array_rand($hubs)]->id;
                
                // Create address for order (only for regular orders, POS can be null)
                $address = null;
                if ($orderType === Order::TYPE_REGULAR) {
                    $province = $provinces->random();
                    $regency = $regencies->where('province_id', $province->id)->random();
                    $district = District::where('regency_id', $regency->id)->inRandomOrder()->first();
                    $village = $district 
                        ? Village::where('district_id', $district->id)->inRandomOrder()->first()
                        : null;
                    
                    if (!$district || !$village) {
                        $district = District::inRandomOrder()->first();
                        $village = Village::where('district_id', $district->id)->inRandomOrder()->first();
                    }
                    
                    if ($district && $village) {
                        $address = Address::create([
                            'id' => (string) Str::uuid(),
                            'user_id' => $distributor->id,
                            'label' => 'Alamat Utama',
                            'recipient_name' => $distributor->name,
                            'phone' => $distributor->phone,
                            'province_id' => $province->id,
                            'regency_id' => $regency->id,
                            'district_id' => $district->id,
                            'village_id' => $village->id,
                            'address_detail' => 'Jl. ' . $regency->name . ' No. ' . ($i + 1),
                            'postal_code' => rand(10000, 99999),
                            'is_default' => true,
                        ]);
                    }
                }
                
                // Select random products (1-5 products per order)
                $orderProducts = $products->random(rand(1, 5));
                $subtotal = 0;
                $orderItems = [];
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 10);
                    $price = (float) $product->price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;
                    
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                    ];
                }
                
                $totalAmount = $subtotal + $shippingCost;
                
                // Set dates based on status
                $paidAt = null;
                $shippedAt = null;
                if ($paymentStatus === 'paid') {
                    // For POS orders, paid_at is usually same as created_at or very close
                    if ($orderType === Order::TYPE_POS) {
                        $paidAt = $orderCreatedAt->copy()->addMinutes(rand(0, 30));
                    } else {
                        $paidAt = $orderCreatedAt->copy()->addDays(rand(0, 2));
                    }
                }
                if (in_array($orderStatus, ['shipped', 'delivered', 'completed'])) {
                    // For POS orders, shipped_at is usually same as created_at or very close
                    if ($orderType === Order::TYPE_POS) {
                        $shippedAt = $orderCreatedAt->copy()->addMinutes(rand(0, 60));
                    } else {
                        $shippedAt = $paidAt 
                            ? $paidAt->copy()->addDays(rand(1, 3)) 
                            : $orderCreatedAt->copy()->addDays(rand(1, 5));
                    }
                }
                
                // Get expedition service (only for regular orders)
                $expedition = $expeditions->random();
                $expServices = $expedition->services;
                $expService = $expServices[array_rand($expServices)] ?? ['code' => 'REG', 'name' => 'Reguler'];
                
                // Create order
                $order = Order::create([
                    'id' => (string) Str::uuid(),
                    'order_number' => $orderNumber,
                    'user_id' => $distributor->id,
                    'order_type' => $orderType,
                    'address_id' => $address?->id,
                    'expedition_id' => $orderType === Order::TYPE_REGULAR ? $expedition->id : null,
                    'expedition_service' => $orderType === Order::TYPE_REGULAR ? ($expService['code'] ?? 'REG') : null,
                    'source_warehouse_id' => $sourceWarehouseId,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $totalAmount,
                    'shipping_address' => $orderType === Order::TYPE_POS ? 'Pembeli Umum' : ($address?->full_address ?? ''),
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'paid_at' => $paidAt,
                    'order_status' => $orderStatus,
                    'tracking_number' => in_array($orderStatus, ['shipped', 'delivered', 'completed']) 
                        ? 'TRK' . rand(100000000, 999999999) 
                        : null,
                    'shipped_at' => $shippedAt,
                    'notes' => rand(0, 1) ? 'Catatan untuk pesanan ini' : null,
                    'points_earned' => 0,
                    'points_credited' => false,
                    'created_at' => $orderCreatedAt,
                    'updated_at' => $orderCreatedAt->copy()->addDays(rand(0, min($randomDay, 5))),
                ]);
                
                // Create order items
                foreach ($orderItems as $item) {
                    OrderItem::create([
                        'id' => (string) Str::uuid(),
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
                
                $distributorOrdersCreated++;
                $totalOrdersCreated++;
            }
            
            $this->command->info("    ✓ Created {$distributorOrdersCreated} orders for {$distributor->name}");
        }
        
        $this->command->info('✓ Created ' . $totalOrdersCreated . ' total orders (' . count($distributors) . ' distributors × 150 orders)');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('Demo data created successfully!');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('  - Hubs (Warehouses): ' . count($hubs));
        $this->command->info('  - Distributors: ' . count($distributors));
        $this->command->info('  - Driippreneurs: ' . count($driippreneurs));
        $this->command->info('  - Orders: ' . $totalOrdersCreated . ' (' . count($distributors) . ' distributors × 150 orders)');
        $this->command->info('  - Warehouse Stocks: ' . WarehouseStock::count());
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('LOGINABLE ACCOUNTS (Password: password)');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('HUB USERS (Warehouse Role):');
        foreach (array_slice($hubs, 0, 5) as $hub) {
            $hubUser = User::where('warehouse_id', $hub->id)->where('role', User::ROLE_WAREHOUSE)->first();
            if ($hubUser) {
                $this->command->info('  - ' . $hubUser->email . ' → ' . $hub->name . ' (' . $hub->regency->name . ')');
            }
        }
        $this->command->info('  ... and ' . (count($hubs) - 5) . ' more hub users');
        $this->command->info('');
        $this->command->info('DISTRIBUTOR USERS (Distributor Role):');
        foreach (array_slice($distributors, 0, 5) as $distributor) {
            $this->command->info('  - ' . $distributor->email . ' → ' . $distributor->name);
        }
        $this->command->info('  ... and ' . (count($distributors) - 5) . ' more distributors');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }

    /**
     * Clean up old demo data, keeping only products and Indonesia regions
     */
    private function cleanupOldData(): void
    {
        // Disable foreign key checks temporarily for faster deletion
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Delete in order to avoid foreign key constraints
            
            // 1. Delete warehouse stock histories first
            if (class_exists(\App\Models\WarehouseStockHistory::class)) {
                $stockHistoriesCount = WarehouseStockHistory::count();
                WarehouseStockHistory::query()->delete();
                $this->command->info("  ✓ Deleted {$stockHistoriesCount} warehouse stock histories");
            }
            
            // 2. Delete order items first (depends on orders and products)
            $orderItemsCount = OrderItem::count();
            OrderItem::query()->delete();
            $this->command->info("  ✓ Deleted {$orderItemsCount} order items");

            // 3. Delete orders (depends on users, addresses, expeditions, warehouses)
            $ordersCount = Order::count();
            Order::query()->delete();
            $this->command->info("  ✓ Deleted {$ordersCount} orders");

            // 4. Delete addresses (depends on users)
            $addressesCount = Address::count();
            Address::query()->delete();
            $this->command->info("  ✓ Deleted {$addressesCount} addresses");

            // 5. Delete carts (depends on users, products, warehouses)
            $cartsCount = Cart::count();
            Cart::query()->delete();
            $this->command->info("  ✓ Deleted {$cartsCount} carts");

            // 6. Delete point withdrawals (depends on users)
            $pointWithdrawalsCount = PointWithdrawal::count();
            PointWithdrawal::query()->delete();
            $this->command->info("  ✓ Deleted {$pointWithdrawalsCount} point withdrawals");

            // 7. Delete warehouse stocks (depends on warehouses and products)
            $warehouseStocksCount = WarehouseStock::count();
            WarehouseStock::query()->delete();
            $this->command->info("  ✓ Deleted {$warehouseStocksCount} warehouse stocks");

            // 8. Delete warehouses (hubs)
            $warehousesCount = Warehouse::count();
            Warehouse::query()->delete();
            $this->command->info("  ✓ Deleted {$warehousesCount} warehouses");

            // 9. Delete users except super_admin and agent (needed for product created_by)
            $usersToKeep = User::whereIn('role', [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_AGENT,
            ])->pluck('id')->toArray();

            // Delete all users with @demo.com email (including warehouse users)
            $demoUsersCount = User::where('email', 'like', '%@demo.com')->count();
            User::where('email', 'like', '%@demo.com')->delete();

            // Also delete any other non-admin/agent users
            $otherUsersCount = User::whereNotIn('id', $usersToKeep)->count();
            User::whereNotIn('id', $usersToKeep)->delete();
            
            $this->command->info("  ✓ Deleted {$demoUsersCount} demo users (@demo.com)");
            $this->command->info("  ✓ Deleted {$otherUsersCount} other users (kept super_admin, agent)");

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('');
        $this->command->info('Data preserved:');
        $this->command->info('  - Products: ' . Product::count());
        $this->command->info('  - Brands: ' . \App\Models\Brand::count());
        $this->command->info('  - Categories: ' . \App\Models\Category::count());
        $this->command->info('  - Price Levels: ' . \App\Models\PriceLevel::count());
        $this->command->info('  - Product Price Levels: ' . \App\Models\ProductPriceLevel::count());
        $this->command->info('  - Expeditions: ' . Expedition::count());
        $this->command->info('  - Indonesia Regions: ' . Province::count() . ' provinces, ' . Regency::count() . ' regencies');
        $this->command->info('');
    }
}


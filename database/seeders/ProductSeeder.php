<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get an agent user to be the creator
        $agent = User::where('role', User::ROLE_AGENT)->first();
        
        if (!$agent) {
            $agent = User::where('role', User::ROLE_SUPER_ADMIN)->first();
        }

        if (!$agent) {
            $this->command->error('No agent or super admin user found! Run UserSeeder first.');
            return;
        }

        // Get or create brands
        $multibev = Brand::where('name', 'Multibev')->first()?->id;
        $fruitmax = Brand::where('name', 'FruitMax')->first()?->id;
        $teazen = Brand::where('name', 'TeaZen')->first()?->id;
        $coffeeking = Brand::where('name', 'CoffeeKing')->first()?->id;

        // Get or create categories
        $coconut = Category::where('name', 'Coconut')->first()?->id;
        $aloevera = Category::where('name', 'Aloe Vera')->first()?->id;
        $juice = Category::where('name', 'Juice')->first()?->id;
        $tea = Category::where('name', 'Tea')->first()?->id;
        $coffee = Category::where('name', 'Coffee')->first()?->id;

        $products = [
            [
                'code' => 'FMF020-CT12',
                'name' => 'MB Cons 1L-Coconut Milk',
                'commercial_name' => 'Coconut Milk Premium',
                'description' => 'Santan kelapa murni berkualitas tinggi',
                'technical_description' => '(In Bottle) FG Multibev',
                'brand_id' => $multibev,
                'size' => '1 L',
                'category_id' => $coconut,
                'unit' => 'BT',
                'price' => 70000,
                'weight' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'FMF020-CT11',
                'name' => 'MB Cons 1L-Coconut Water',
                'commercial_name' => 'Coconut Water Fresh',
                'description' => 'Air kelapa segar alami',
                'technical_description' => '(In Bottle) FG Multibev',
                'brand_id' => $multibev,
                'size' => '1 L',
                'category_id' => $coconut,
                'unit' => 'BT',
                'price' => 65000,
                'weight' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'FMF020-CT02',
                'name' => 'MB Cons 1L-Coconut Milk Pack',
                'commercial_name' => 'Coconut Milk Pack',
                'description' => 'Santan kelapa dalam kemasan praktis',
                'technical_description' => 'FG Multibev',
                'brand_id' => $multibev,
                'size' => '1 L',
                'category_id' => $coconut,
                'unit' => 'PK',
                'price' => 60000,
                'weight' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'FMF030-AL01',
                'name' => 'Premium Aloe Vera Drink',
                'commercial_name' => 'Aloe Vera Drink',
                'description' => 'Minuman lidah buaya segar',
                'technical_description' => 'Aloe Vera Extract',
                'brand_id' => $multibev,
                'size' => '500 ML',
                'category_id' => $aloevera,
                'unit' => 'BT',
                'price' => 35000,
                'weight' => 500,
                'status' => 'active',
            ],
            [
                'code' => 'FMF040-MG01',
                'name' => 'Mango Juice Premium',
                'commercial_name' => 'Mango Juice',
                'description' => 'Jus mangga asli tanpa pengawet',
                'technical_description' => 'Natural Mango Extract',
                'brand_id' => $fruitmax,
                'size' => '1 L',
                'category_id' => $juice,
                'unit' => 'BT',
                'price' => 45000,
                'weight' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'FMF040-OR01',
                'name' => 'Orange Juice Fresh',
                'commercial_name' => 'Orange Juice',
                'description' => 'Jus jeruk segar berkualitas',
                'technical_description' => 'Fresh Orange Extract',
                'brand_id' => $fruitmax,
                'size' => '1 L',
                'category_id' => $juice,
                'unit' => 'BT',
                'price' => 42000,
                'weight' => 1000,
                'status' => 'active',
            ],
            [
                'code' => 'FMF050-GR01',
                'name' => 'Green Tea Matcha',
                'commercial_name' => 'Matcha Green Tea',
                'description' => 'Teh hijau matcha premium Jepang',
                'technical_description' => 'Premium Matcha Powder',
                'brand_id' => $teazen,
                'size' => '500 ML',
                'category_id' => $tea,
                'unit' => 'BT',
                'price' => 28000,
                'weight' => 500,
                'status' => 'active',
            ],
            [
                'code' => 'FMF050-BL01',
                'name' => 'Black Tea Original',
                'commercial_name' => 'Black Tea',
                'description' => 'Teh hitam original',
                'technical_description' => 'Ceylon Black Tea',
                'brand_id' => $teazen,
                'size' => '500 ML',
                'category_id' => $tea,
                'unit' => 'BT',
                'price' => 22000,
                'weight' => 500,
                'status' => 'active',
            ],
            [
                'code' => 'FMF060-CF01',
                'name' => 'Cold Brew Coffee',
                'commercial_name' => 'Cold Brew',
                'description' => 'Kopi cold brew premium',
                'technical_description' => 'Arabica Cold Brew',
                'brand_id' => $coffeeking,
                'size' => '250 ML',
                'category_id' => $coffee,
                'unit' => 'BT',
                'price' => 35000,
                'weight' => 250,
                'status' => 'active',
            ],
            [
                'code' => 'FMF060-LT01',
                'name' => 'Latte Ready to Drink',
                'commercial_name' => 'RTD Latte',
                'description' => 'Latte siap minum',
                'technical_description' => 'Milk + Espresso',
                'brand_id' => $coffeeking,
                'size' => '250 ML',
                'category_id' => $coffee,
                'unit' => 'BT',
                'price' => 32000,
                'weight' => 250,
                'status' => 'active',
            ],
        ];

        $createdProducts = [];
        foreach ($products as $data) {
            $existing = Product::where('code', $data['code'])->first();
            if ($existing) {
                $this->command->info('Product already exists: ' . $data['code']);
                $createdProducts[] = $existing;
                continue;
            }

            $product = Product::create(array_merge($data, [
                'created_by' => $agent->id,
            ]));

            $createdProducts[] = $product;
            $this->command->info('Created: [' . $product->code . '] ' . $product->name . ' (ID: ' . $product->id . ')');
        }

        $this->command->info('');
        $this->command->info('Total products: ' . Product::count());

        // Seed warehouse stock for all products
        $this->seedWarehouseStock($createdProducts);
    }

    /**
     * Seed warehouse stock for all products
     */
    private function seedWarehouseStock(array $products): void
    {
        $warehouses = Warehouse::all();

        if ($warehouses->isEmpty()) {
            $this->command->warn('No warehouses found. Skip seeding warehouse stock.');
            return;
        }

        $this->command->info('');
        $this->command->info('Seeding warehouse stock...');

        foreach ($warehouses as $warehouse) {
            $stockCount = 0;
            foreach ($products as $product) {
                // Check if stock already exists
                $existingStock = WarehouseStock::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingStock) {
                    continue;
                }

                // Random stock between 50 and 200
                $stock = rand(50, 200);

                WarehouseStock::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'stock' => $stock,
                ]);

                $stockCount++;
            }

            $this->command->info("  {$warehouse->name}: {$stockCount} products stocked");
        }
    }
}

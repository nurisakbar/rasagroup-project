<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Brands
        $brands = [
            ['name' => 'Multibev', 'description' => 'Multi Beverage Company'],
            ['name' => 'FruitMax', 'description' => 'Premium Fruit Products'],
            ['name' => 'TeaZen', 'description' => 'Quality Tea Products'],
            ['name' => 'CoffeeKing', 'description' => 'Premium Coffee Products'],
            ['name' => 'NaturalDrink', 'description' => 'Natural & Organic Drinks'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(
                ['name' => $brandData['name']],
                [
                    'slug' => Str::slug($brandData['name']),
                    'description' => $brandData['description'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Brands seeded: ' . count($brands));

        // Seed Categories
        $categories = [
            ['name' => 'Coconut', 'description' => 'Coconut based products'],
            ['name' => 'Aloe Vera', 'description' => 'Aloe vera products'],
            ['name' => 'Juice', 'description' => 'Fresh juices'],
            ['name' => 'Tea', 'description' => 'Tea based drinks'],
            ['name' => 'Coffee', 'description' => 'Coffee products'],
            ['name' => 'Water', 'description' => 'Water and mineral drinks'],
            ['name' => 'Energy Drink', 'description' => 'Energy drinks'],
            ['name' => 'Syrup', 'description' => 'Syrups and concentrates'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Categories seeded: ' . count($categories));
    }
}


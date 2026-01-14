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
            ['name' => 'Coconut', 'icon' => 'fa-leaf', 'description' => 'Coconut based products'],
            ['name' => 'Aloe Vera', 'icon' => 'fa-envira', 'description' => 'Aloe vera products'],
            ['name' => 'Juice', 'icon' => 'fa-glass', 'description' => 'Fresh juices'],
            ['name' => 'Tea', 'icon' => 'fa-coffee', 'description' => 'Tea based drinks'],
            ['name' => 'Coffee', 'icon' => 'fa-coffee', 'description' => 'Coffee products'],
            ['name' => 'Water', 'icon' => 'fa-tint', 'description' => 'Water and mineral drinks'],
            ['name' => 'Energy Drink', 'icon' => 'fa-bolt', 'description' => 'Energy drinks'],
            ['name' => 'Syrup', 'icon' => 'fa-flask', 'description' => 'Syrups and concentrates'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                    'icon' => $categoryData['icon'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Categories seeded: ' . count($categories));
    }
}


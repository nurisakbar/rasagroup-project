<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Populate existing products
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            $slug = \Illuminate\Support\Str::slug($product->display_name ?: $product->name);
            
            // Check for uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (\App\Models\Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $product->slug = $slug;
            $product->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};

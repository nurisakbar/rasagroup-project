<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Populate existing warehouses
        $warehouses = \App\Models\Warehouse::all();
        foreach ($warehouses as $warehouse) {
            $slug = Str::slug($warehouse->name);
            
            // Check for uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (\App\Models\Warehouse::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $warehouse->slug = $slug;
            $warehouse->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};

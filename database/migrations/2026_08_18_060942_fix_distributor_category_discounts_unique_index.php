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
        Schema::table('distributor_category_discounts', function (Blueprint $table) {
            // Drop the old incorrectly named unique constraint if it exists
            if (Schema::hasIndex('distributor_category_discounts', 'distributor_category_discounts_distributor_id_category_id_unique')) {
                $table->dropUnique('distributor_category_discounts_distributor_id_category_id_unique');
            }

            // Also check if the array-based index name exists
            if (Schema::hasIndex('distributor_category_discounts', ['distributor_id', 'category_id'])) {
                try {
                    $table->dropUnique(['distributor_id', 'category_id']);
                } catch (\Exception $e) {
                    // Ignore if it was already dropped or doesn't exist
                }
            }

            // Ensure the proper unique constraint including brand_id exists
            if (!Schema::hasIndex('distributor_category_discounts', 'dist_brand_cat_unique')) {
                $table->unique(['distributor_id', 'brand_id', 'category_id'], 'dist_brand_cat_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_category_discounts', function (Blueprint $table) {
            // Revert back if necessary
            if (Schema::hasIndex('distributor_category_discounts', 'dist_brand_cat_unique')) {
                $table->dropUnique('dist_brand_cat_unique');
            }

            if (!Schema::hasIndex('distributor_category_discounts', 'distributor_category_discounts_distributor_id_category_id_unique')) {
                $table->unique(['distributor_id', 'category_id'], 'distributor_category_discounts_distributor_id_category_id_unique');
            }
        });
    }
};

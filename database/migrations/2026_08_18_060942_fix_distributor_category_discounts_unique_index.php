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
                try {
                    // Drop foreign key first because the unique index might be supporting it
                    $table->dropForeign(['distributor_id']);
                } catch (\Exception $e) {}
                
                try {
                    $table->dropUnique('distributor_category_discounts_distributor_id_category_id_unique');
                } catch (\Exception $e) {}
                
                try {
                    $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
                } catch (\Exception $e) {}
            }

            // Also check if the array-based index name exists
            $indexName = $table->getTable() . '_distributor_id_category_id_unique';
            if (Schema::hasIndex('distributor_category_discounts', $indexName)) {
                try {
                    $table->dropForeign(['distributor_id']);
                    $table->dropUnique(['distributor_id', 'category_id']);
                    $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
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
                try {
                    // Drop foreign key first because the unique index might be supporting it
                    $table->dropForeign(['distributor_id']);
                } catch (\Exception $e) {}
                
                try {
                    $table->dropUnique('dist_brand_cat_unique');
                } catch (\Exception $e) {}
                
                try {
                    $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
                } catch (\Exception $e) {}
            }

            if (!Schema::hasIndex('distributor_category_discounts', 'distributor_category_discounts_distributor_id_category_id_unique')) {
                $table->unique(['distributor_id', 'category_id'], 'distributor_category_discounts_distributor_id_category_id_unique');
            }
        });
    }
};

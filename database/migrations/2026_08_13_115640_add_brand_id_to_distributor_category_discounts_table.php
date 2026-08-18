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
        if (!Schema::hasColumn('distributor_category_discounts', 'brand_id')) {
            Schema::table('distributor_category_discounts', function (Blueprint $table) {
                $table->uuid('brand_id')->after('distributor_id')->nullable();
                
                // Drop the old unique constraint and add the new one
                $table->dropUnique(['distributor_id', 'category_id']);
                $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
                $table->unique(['distributor_id', 'brand_id', 'category_id'], 'dist_brand_cat_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('distributor_category_discounts', 'brand_id')) {
            Schema::table('distributor_category_discounts', function (Blueprint $table) {
                $table->dropForeign(['brand_id']);
                $table->dropUnique('dist_brand_cat_unique');
                
                $table->unique(['distributor_id', 'category_id']);
                $table->dropColumn('brand_id');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diskon per brand & kategori yang diberikan distributor kepada outlet rekrutannya.
 * Berlaku hanya saat outlet belanja dari hub milik distributor tersebut, dan dibatasi
 * users.outlet_discount_max milik distributor (diatur admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_category_discounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('outlet_id');
            $table->uuid('distributor_id');
            $table->uuid('brand_id');
            $table->uuid('category_id');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['outlet_id', 'brand_id', 'category_id'], 'outlet_brand_cat_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            // Batas diskon yang boleh diberikan distributor ke outlet (null/0 = belum boleh memberi diskon).
            $table->decimal('outlet_discount_max', 5, 2)->nullable()->after('distributor_sales_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('outlet_discount_max');
        });

        Schema::dropIfExists('outlet_category_discounts');
    }
};

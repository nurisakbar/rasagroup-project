<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penjualan distributor yang dilaporkan sistem eksternal mereka. Hanya pencatatan:
     * sengaja terpisah dari `orders` agar tidak tersentuh QAD, WMS, stok, finance, dan GMV.
     */
    public function up(): void
    {
        Schema::create('distributor_api_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('key_prefix', 12);
            $table->string('key_hash', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('distributor_external_sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distributor_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('api_client_id')->nullable()->constrained('distributor_api_clients')->nullOnDelete();
            $table->string('external_order_number', 100);
            $table->dateTime('sold_at');
            $table->date('sold_date')->index();
            $table->string('status', 20)->default('confirmed')->index();
            $table->string('customer_name');
            $table->string('customer_code', 100)->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->json('raw_payload');
            $table->timestamps();

            $table->unique(['distributor_id', 'external_order_number'], 'distributor_external_sales_number_unique');
        });

        Schema::create('distributor_external_sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sale_id')->constrained('distributor_external_sales')->cascadeOnDelete();
            $table->string('external_product_code', 100)->index();
            $table->string('product_name')->nullable();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->string('uom', 20)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_external_sale_items');
        Schema::dropIfExists('distributor_external_sales');
        Schema::dropIfExists('distributor_api_clients');
    }
};

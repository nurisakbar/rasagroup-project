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
        Schema::create('price_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100); // Nama level (misalnya: Level 1, Level 2, Silver, Gold)
            $table->text('description')->nullable(); // Deskripsi level
            $table->decimal('discount_percentage', 5, 2)->default(0); // Persentase diskon default (misalnya: 5.00 untuk 5%)
            $table->integer('order')->default(0); // Urutan untuk sorting
            $table->boolean('is_active')->default(true); // Status aktif/tidak
            $table->timestamps();

            $table->index('is_active');
            $table->index('order');
        });

        // Tabel pivot untuk relasi many-to-many antara products dan price_levels
        // Memungkinkan harga khusus per produk per level (optional override)
        Schema::create('product_price_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('price_level_id');
            $table->decimal('price', 12, 2)->nullable(); // Harga khusus (jika null, gunakan discount_percentage dari price_levels)
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('price_level_id')->references('id')->on('price_levels')->onDelete('cascade');
            $table->unique(['product_id', 'price_level_id']); // Satu produk hanya bisa punya satu harga per level
            $table->index('product_id');
            $table->index('price_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_levels');
        Schema::dropIfExists('price_levels');
    }
};

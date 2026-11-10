<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master harga per warehouse, berlaku per tanggal. Baris tidak pernah diubah:
     * harga baru = baris baru, sehingga riwayat harga utuh.
     *
     * order_items.catalog_price mengunci harga katalog saat penjualan, supaya order lama
     * tidak ikut berubah ketika harga produk atau master harga berubah.
     */
    public function up(): void
    {
        Schema::create('warehouse_product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->string('source', 20)->default('manual');
            $table->string('note')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['warehouse_id', 'product_id', 'effective_from'], 'warehouse_product_prices_lookup');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('catalog_price', 12, 2)->nullable()->after('price');
            $table->foreignUuid('price_master_id')->nullable()->after('catalog_price')
                ->constrained('warehouse_product_prices')->nullOnDelete();
        });

        // Order lama: harga katalog asli tidak tersimpan, jadi dikunci pada harga produk saat migrasi.
        DB::statement('UPDATE order_items oi JOIN products p ON p.id = oi.product_id SET oi.catalog_price = p.price WHERE oi.catalog_price IS NULL');
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('price_master_id');
            $table->dropColumn('catalog_price');
        });

        Schema::dropIfExists('warehouse_product_prices');
    }
};

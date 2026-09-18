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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source_qad_location_code')->nullable()->after('source_warehouse_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('allocated_batches')->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('source_qad_location_code');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('allocated_batches');
        });
    }
};

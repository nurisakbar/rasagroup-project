<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('order_uom', 16)->nullable()->after('quantity');
            $table->unsignedInteger('quantity_ordered')->nullable()->after('order_uom');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('order_uom', 16)->nullable()->after('quantity');
            $table->unsignedInteger('quantity_ordered')->nullable()->after('order_uom');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['order_uom', 'quantity_ordered']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['order_uom', 'quantity_ordered']);
        });
    }
};

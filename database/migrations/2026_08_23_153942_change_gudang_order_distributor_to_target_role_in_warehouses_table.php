<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'gudang_order_distributor')) {
                $table->dropColumn('gudang_order_distributor');
            }
            if (!Schema::hasColumn('warehouses', 'target_role')) {
                $table->string('target_role')->default('umum')->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'target_role')) {
                $table->dropColumn('target_role');
            }
            if (!Schema::hasColumn('warehouses', 'gudang_order_distributor')) {
                $table->boolean('gudang_order_distributor')->default(false)->after('is_active');
            }
        });
    }
};
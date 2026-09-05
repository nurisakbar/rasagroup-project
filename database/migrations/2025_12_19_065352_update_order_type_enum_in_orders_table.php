<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update enum order_type to support POS (offline) orders.
     */
    public function up(): void
    {
        // MySQL enum needs raw ALTER TABLE to add new value
        Schema::table('orders', function (Blueprint $table) {
            //
        });

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE `orders`
                MODIFY `order_type` ENUM('regular', 'distributor', 'pos') NOT NULL DEFAULT 'regular'
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * Revert enum to original values (regular, distributor).
     * Warning: if there are existing 'pos' rows, this will fail unless cleaned first.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });

        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE `orders`
            MODIFY `order_type` ENUM('regular', 'distributor') NOT NULL DEFAULT 'regular'
        ");
    }
};

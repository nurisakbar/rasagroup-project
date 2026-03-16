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
        Schema::table('discount_tiers', function (Blueprint $table) {
            $table->renameColumn('min_purchase', 'min_quantity');
        });
        
        Schema::table('discount_tiers', function (Blueprint $table) {
            $table->integer('min_quantity')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_tiers', function (Blueprint $table) {
            $table->decimal('min_quantity', 15, 2)->change();
        });

        Schema::table('discount_tiers', function (Blueprint $table) {
            $table->renameColumn('min_quantity', 'min_purchase');
        });
    }
};

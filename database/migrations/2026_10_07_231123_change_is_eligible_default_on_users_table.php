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
        // Update existing nulls first to prevent invalid use of NULL value
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('is_eligible')
            ->update(['is_eligible' => 0]);

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_eligible')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_eligible')->nullable()->default(null)->change();
        });
    }
};

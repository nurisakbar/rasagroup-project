<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign keys first to prevent constraint failures
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['distributor_province_id']);
            $table->dropForeign(['distributor_regency_id']);
        });

        // Drop the tables in reverse dependency order
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Too complex to reverse, ideally restoring from backup or rerunning indoregion
    }
};

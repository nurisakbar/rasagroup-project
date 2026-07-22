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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['province_id']);
            
            // Note: We might also want to drop district_id if it exists
            // Let's just drop the ones that cause constraints
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['province_id']);
        });

        // Change column types to match Regencies and Provinces (varchar/char)
        // Note: Using raw queries because changing bigint to varchar with doctrine can be problematic
        DB::statement('ALTER TABLE warehouses MODIFY regency_id VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE warehouses MODIFY province_id VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE addresses MODIFY regency_id VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE addresses MODIFY province_id VARCHAR(255) NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this would require recreating RajaOngkir foreign keys and converting back to bigint
    }
};

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
            // Drop foreign key if it exists. Ignore errors if it doesn't.
            try { $table->dropForeign(['district_id']); } catch (\Exception $e) {}
        });

        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropForeign(['district_id']); } catch (\Exception $e) {}
        });

        DB::statement('ALTER TABLE warehouses MODIFY district_id VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE addresses MODIFY district_id VARCHAR(255) NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

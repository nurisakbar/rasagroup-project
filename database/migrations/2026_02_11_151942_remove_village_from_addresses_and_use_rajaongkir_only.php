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
        Schema::table('addresses', function (Blueprint $table) {
            // Drop village foreign key and column
            if (Schema::hasColumn('addresses', 'village_id')) {
                try {
                    $table->dropForeign(['village_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('village_id');
            }
        });
        
        // Drop old district foreign key using raw SQL
        try {
            DB::statement('ALTER TABLE addresses DROP FOREIGN KEY addresses_district_id_foreign');
        } catch (\Exception $e) {
            // Foreign key doesn't exist, that's okay
        }
        
        Schema::table('addresses', function (Blueprint $table) {
            // Modify column type to unsignedBigInteger for RajaOngkir
            $table->unsignedBigInteger('district_id')->nullable()->change();
        });
        
        // Clean up invalid district_id values (set to NULL if not exists in raja_ongkir_districts)
        DB::statement('
            UPDATE addresses 
            SET district_id = NULL 
            WHERE district_id IS NOT NULL 
            AND district_id NOT IN (SELECT id FROM raja_ongkir_districts)
        ');
        
        Schema::table('addresses', function (Blueprint $table) {
            // Add foreign key to raja_ongkir_districts
            $table->foreign('district_id')
                ->references('id')
                ->on('raja_ongkir_districts')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Drop RajaOngkir foreign key
            $table->dropForeign(['district_id']);
            
            // Restore village_id
            $table->string('village_id', 10)->nullable()->after('district_id');
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
            
            // Revert district_id
            $table->string('district_id', 7)->nullable()->change();
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
        });
    }
};

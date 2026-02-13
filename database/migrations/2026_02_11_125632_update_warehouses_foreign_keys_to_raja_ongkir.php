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
        // Drop foreign keys only if they exist
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'warehouses' AND TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE 'warehouses_%_foreign'");
        $fkNames = array_map(fn($fk) => $fk->CONSTRAINT_NAME, $foreignKeys);

        Schema::table('warehouses', function (Blueprint $table) use ($fkNames) {
            if (in_array('warehouses_province_id_foreign', $fkNames)) { $table->dropForeign(['province_id']); }
            if (in_array('warehouses_regency_id_foreign', $fkNames)) { $table->dropForeign(['regency_id']); }
        });

        // First, make columns nullable so we can cleanse data
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('province_id')->nullable()->change();
            $table->string('regency_id')->nullable()->change();
            $table->string('district_id')->nullable()->change();
        });

        // Convert empty strings and invalid IDs to NULL to avoid foreign key violations
        DB::table('warehouses')->where('province_id', '')->update(['province_id' => null]);
        DB::table('warehouses')->where('regency_id', '')->update(['regency_id' => null]);
        DB::table('warehouses')->where('district_id', '')->update(['district_id' => null]);

        // Cleanse invalid IDs that don't exist in RajaOngkir
        DB::table('warehouses')->whereNotExists(function($q){
            $q->select(DB::raw(1))->from('raja_ongkir_provinces')->whereRaw('raja_ongkir_provinces.id = warehouses.province_id');
        })->update(['province_id' => null]);

        DB::table('warehouses')->whereNotExists(function($q){
            $q->select(DB::raw(1))->from('raja_ongkir_cities')->whereRaw('raja_ongkir_cities.id = warehouses.regency_id');
        })->update(['regency_id' => null]);

        DB::table('warehouses')->whereNotExists(function($q){
            $q->select(DB::raw(1))->from('raja_ongkir_districts')->whereRaw('raja_ongkir_districts.id = warehouses.district_id');
        })->update(['district_id' => null]);

        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('regency_id')->nullable()->change();
            $table->unsignedBigInteger('district_id')->nullable()->change();

            $table->foreign('province_id')->references('id')->on('raja_ongkir_provinces')->onDelete('cascade');
            $table->foreign('regency_id')->references('id')->on('raja_ongkir_cities')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('raja_ongkir_districts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['district_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('province_id', 2)->change();
            $table->string('regency_id', 4)->change();
            $table->string('district_id')->change();

            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->foreign('regency_id')->references('id')->on('regencies')->onDelete('cascade');
        });
    }
};

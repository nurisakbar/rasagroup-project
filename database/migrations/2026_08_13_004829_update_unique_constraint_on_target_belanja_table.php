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
        Schema::table('target_belanja', function (Blueprint $table) {
            $table->dropUnique('target_belanja_distributor_id_bulan_tahun_unique');
            $table->unique(['distributor_id', 'brand_id', 'bulan_tahun'], 'target_belanja_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_belanja', function (Blueprint $table) {
            $table->dropUnique('target_belanja_unique');
            $table->unique(['distributor_id', 'bulan_tahun']);
        });
    }
};

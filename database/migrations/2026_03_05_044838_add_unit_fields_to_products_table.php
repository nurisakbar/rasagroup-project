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
        Schema::table('products', function (Blueprint $table) {
            $table->string('large_unit')->nullable()->after('unit');
            $table->string('small_unit')->nullable()->after('large_unit');
            $table->integer('unit_conversion')->default(1)->after('small_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['large_unit', 'small_unit', 'unit_conversion']);
        });
    }
};

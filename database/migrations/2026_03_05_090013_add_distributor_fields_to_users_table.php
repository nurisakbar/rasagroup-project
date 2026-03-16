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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('term_of_payment')->nullable()->after('price_level_id')->comment('TOP in days');
            $table->decimal('monthly_target', 15, 2)->nullable()->after('term_of_payment')->comment('Monthly purchase target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['term_of_payment', 'monthly_target']);
        });
    }
};

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
        Schema::table('information_channels', function (Blueprint $table) {
            $table->enum('target_audience', ['all', 'distributor', 'customer'])->default('all')->after('description');
            $table->date('start_date')->nullable()->after('target_audience');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('information_channels', function (Blueprint $table) {
            $table->dropColumn(['target_audience', 'start_date', 'end_date']);
        });
    }
};

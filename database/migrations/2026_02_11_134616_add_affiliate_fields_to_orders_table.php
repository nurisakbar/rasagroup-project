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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('affiliate_id')->nullable()->after('points_credited')
                ->constrained('users')
                ->onDelete('set null');
            $table->integer('affiliate_points')->default(0)->after('affiliate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropColumn(['affiliate_id', 'affiliate_points']);
        });
    }
};

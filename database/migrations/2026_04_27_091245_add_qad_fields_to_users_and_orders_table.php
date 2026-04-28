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
            $table->string('qad_customer_code')->nullable()->after('referral_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('qad_so_number')->nullable()->after('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('qad_customer_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qad_so_number');
        });
    }
};

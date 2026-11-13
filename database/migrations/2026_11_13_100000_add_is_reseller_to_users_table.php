<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda reseller: user biasa (buyer) yang ditandai admin sebagai reseller.
 * Reseller mendapat tier diskon MOQ "Subdist / Reseller".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_reseller')->default(false)->after('sub_role')->index();
            $table->timestamp('reseller_marked_at')->nullable()->after('is_reseller');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_reseller', 'reseller_marked_at']);
        });
    }
};

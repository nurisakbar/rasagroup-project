<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bayar tempo (TOP) ke distributor oleh subdist & outlet milik distributor: lama tempo (7/14/30 hari) dan tanggal jatuh tempo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('payment_term_days')->nullable()->after('payment_distributor_id');
            $table->date('payment_due_at')->nullable()->after('payment_term_days');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_term_days', 'payment_due_at']);
        });
    }
};

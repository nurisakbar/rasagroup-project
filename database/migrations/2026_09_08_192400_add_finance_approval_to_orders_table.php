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
            $table->timestamp('finance_approved_at')->nullable()->after('payment_submitted_at');
            $table->uuid('finance_approved_by')->nullable()->after('finance_approved_at');
            $table->foreign('finance_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['finance_approved_by']);
            $table->dropColumn(['finance_approved_at', 'finance_approved_by']);
        });
    }
};

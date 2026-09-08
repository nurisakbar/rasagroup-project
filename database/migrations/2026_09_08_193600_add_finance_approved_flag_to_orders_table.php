<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('finance_approved')->default(false)->after('payment_submitted_at');
        });

        // Sudah lunas / TOP approved sebelumnya → finance_approved = 1
        DB::table('orders')
            ->whereIn('payment_status', ['paid', 'term_of_payment'])
            ->orWhereNotNull('finance_approved_at')
            ->update(['finance_approved' => true]);

        // TOP yang masih pending → pastikan 0
        DB::table('orders')
            ->where('payment_method', 'term_of_payment')
            ->where('payment_status', 'pending')
            ->whereNull('finance_approved_at')
            ->update(['finance_approved' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('finance_approved');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekening milik distributor. Tampil saat subdist dan outlet milik distributor membayar dengan
 * metode "Transfer ke rekening distributor". orders.payment_distributor_id = distributor penerima pembayaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('distributor_id');
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['distributor_id', 'bank_name', 'account_number'], 'dist_bank_accounts_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('payment_distributor_id')->nullable()->after('payment_method');
            $table->foreign('payment_distributor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_distributor_id']);
            $table->dropColumn('payment_distributor_id');
        });
        Schema::dropIfExists('distributor_bank_accounts');
    }
};

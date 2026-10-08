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
            $table->string('zoho_customer_id')->nullable()->after('email');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('zoho_item_id')->nullable()->after('code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('zoho_salesorder_id')->nullable()->after('total_amount');
            $table->string('zoho_invoice_id')->nullable()->after('zoho_salesorder_id');
            $table->string('zoho_payment_id')->nullable()->after('zoho_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('zoho_customer_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('zoho_item_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['zoho_salesorder_id', 'zoho_invoice_id', 'zoho_payment_id']);
        });
    }
};

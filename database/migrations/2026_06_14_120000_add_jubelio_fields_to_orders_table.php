<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('jubelio_salesorder_id')->nullable()->after('qid_sales_order_number');
            $table->string('jubelio_salesorder_no', 50)->nullable()->after('jubelio_salesorder_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['jubelio_salesorder_id', 'jubelio_salesorder_no']);
        });
    }
};

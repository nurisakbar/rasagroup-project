<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Order subdist: dipenuhi dari stok gudang distributor, tidak dikirim ke QAD, Jubelio, dan WMS.
            $table->boolean('fulfilled_by_distributor')->default(false)->after('source_warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('fulfilled_by_distributor');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales (milik distributor) yang merekrut outlet/subdist lewat link pendaftaran per sales.
 * Subdist tetap tercatat ke distributor lewat sales_code (kode distributor); outlet lewat kode sales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('recruited_by_sales_id')->nullable()->after('distributor_id');
            $table->foreign('recruited_by_sales_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['recruited_by_sales_id']);
            $table->dropColumn('recruited_by_sales_id');
        });
    }
};

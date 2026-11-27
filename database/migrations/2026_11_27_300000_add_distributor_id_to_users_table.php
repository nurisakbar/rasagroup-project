<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales milik distributor disimpan di tabel users yang sama dengan Data Sales admin (role sales),
 * ditandai distributor_id = distributor pemiliknya. Sales perusahaan: distributor_id kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('distributor_id')->nullable()->after('sales_code');
            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['role', 'distributor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropIndex(['role', 'distributor_id']);
            $table->dropColumn('distributor_id');
        });
    }
};

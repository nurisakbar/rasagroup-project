<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hub asal outlet/reseller: hub milik sales yang kodenya dipakai saat daftar.
 * Tetap tercatat walau outlet diubah menjadi reseller; dipakai untuk membatasi data di panel Admin Hub.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('hub_id')->nullable()->after('warehouse_id')->index();
            $table->foreign('hub_id')->references('id')->on('warehouses')->nullOnDelete();
        });

        // Isi untuk outlet/reseller lama yang sales-nya sudah punya hub.
        DB::table('users as u')
            ->join('users as s', function ($join) {
                $join->on('s.sales_code', '=', 'u.sales_code')->where('s.role', 'sales')->whereNotNull('s.warehouse_id');
            })
            ->whereIn('u.role', ['outlet', 'buyer'])
            ->whereNull('u.hub_id')
            ->update(['u.hub_id' => DB::raw('s.warehouse_id')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hub_id']);
            $table->dropColumn('hub_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu distributor boleh punya beberapa gudang (sumber kirim / titik ambil subdist).
        Schema::table('warehouses', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouses', 'owner_distributor_id')) {
                $table->uuid('owner_distributor_id')->nullable()->after('is_active')->index();
            }
        });

        // Gudang utama yang sudah ada: pemiliknya distributor yang memakainya.
        // Gudang yang dipakai lebih dari satu distributor dilewati (kepemilikan ambigu).
        $shared = DB::table('users')->where('role', 'distributor')->whereNotNull('warehouse_id')
            ->select('warehouse_id')->groupBy('warehouse_id')->havingRaw('COUNT(*) > 1')->pluck('warehouse_id');

        DB::table('users')->where('role', 'distributor')->whereNotNull('warehouse_id')
            ->whereNotIn('warehouse_id', $shared)
            ->select('id', 'warehouse_id')->orderBy('id')
            ->each(function ($u) {
                DB::table('warehouses')->where('id', $u->warehouse_id)->whereNull('owner_distributor_id')
                    ->update(['owner_distributor_id' => $u->id]);
            });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'owner_distributor_id')) {
                $table->dropColumn('owner_distributor_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kode sales milik distributor sendiri, dipakai untuk link pendaftaran outlet.
     * Berbeda dari users.sales_code, yang pada distributor berisi kode sales PIC-nya.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('distributor_sales_code', 20)->nullable()->unique()->after('sales_code');
        });

        $sequence = 0;

        DB::table('users')
            ->where('role', 'distributor')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($id) use (&$sequence) {
                $sequence++;

                DB::table('users')->where('id', $id)->update([
                    'distributor_sales_code' => 'DST' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['distributor_sales_code']);
            $table->dropColumn('distributor_sales_code');
        });
    }
};

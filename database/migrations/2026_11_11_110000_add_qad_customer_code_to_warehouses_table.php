<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kode customer QAD per hub. Bila diisi, sales order dari hub ini dikirim ke QAD memakai customer
 * tersebut (customer outlet tidak dibuat). Diisi awal sesuai aturan yang sebelumnya tertulis di kode:
 * Hub Bekasi (FG008) = HUB00002, Hub Maggiore (FG007) = HUB00001.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('qad_customer_code', 50)->nullable()->after('qad_location_code');
        });

        DB::table('warehouses')
            ->where(fn ($q) => $q->where('qad_location_code', 'FG008')->orWhere('name', 'like', '%bekasi%'))
            ->update(['qad_customer_code' => 'HUB00002']);

        DB::table('warehouses')
            ->where(fn ($q) => $q->where('qad_location_code', 'FG007')->orWhere('name', 'like', '%maggiore%'))
            ->update(['qad_customer_code' => 'HUB00001']);
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('qad_customer_code');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Centang "Kirim ke Jubelio" per hub: sales order dari hub yang dicentang dikirim ke Jubelio.
 * Diisi awal sesuai aturan yang sebelumnya tertulis di kode (gudang Ecommerce, Hub Bekasi, Hub Maggiore).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('send_to_jubelio')->default(false)->after('is_active');
        });

        $ids = [];
        foreach (DB::table('warehouses')->get(['id', 'name', 'kode_hub', 'qad_location_code', 'target_role']) as $w) {
            $name = strtolower((string) $w->name);
            $kodeHub = strtoupper((string) $w->kode_hub);
            $location = strtoupper((string) $w->qad_location_code);
            $roles = json_decode((string) $w->target_role, true);
            $roles = is_array($roles) ? $roles : [(string) $w->target_role];

            $isBekasi = str_contains($name, 'bekasi') || $location === 'FG008' || in_array($kodeHub, ['WHRG002', 'FG008'], true);
            $isMaggiore = str_contains($name, 'maggiore') || $location === 'FG007' || $kodeHub === 'WHMPRG003';
            $isEcomm = str_contains($name, 'ecomm') || str_contains(strtolower($kodeHub), 'ecomm') || in_array('ecommerce', $roles, true);

            if ($isBekasi || $isMaggiore || $isEcomm) {
                $ids[] = $w->id;
            }
        }

        if ($ids !== []) {
            DB::table('warehouses')->whereIn('id', $ids)->update(['send_to_jubelio' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('send_to_jubelio');
        });
    }
};

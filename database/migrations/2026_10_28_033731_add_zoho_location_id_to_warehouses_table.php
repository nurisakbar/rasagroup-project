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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('zoho_location_id')->nullable()->after('qad_location_code');
        });

        // Set default mapping for active hubs to their Zoho location ID
        $mappings = [
            'WHRG002' => '7386545000004555006', // Hub Bekasi
            'FG008' => '7386545000004555006',   // Hub Bekasi
            'WHMPRG003' => '7386545000004555140', // Hub Maggiore
            'WHECOMM1' => '7386545000004535096',  // ECommerce Tomang
            'WHEV001' => '7386545000004535096',   // ECommerce Tomang
            'WHSBY001' => '7386545000004544047',  // ECommerce Surabaya
            'WHMPSBY' => '7386545000004544047',   // ECommerce Surabaya
            'WHMPRG001' => '7386545000000093096', // DC MM2100 -> Head Office (Zoho limit fallback)
            'FG001' => '7386545000000093096',     // DC MM2100 -> Head Office
        ];

        foreach ($mappings as $code => $zohoId) {
            \Illuminate\Support\Facades\DB::table('warehouses')
                ->where('kode_hub', $code)
                ->orWhere('qad_location_code', $code)
                ->update(['zoho_location_id' => $zohoId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('zoho_location_id');
        });
    }
};

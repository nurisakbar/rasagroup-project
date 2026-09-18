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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('wms_so_status')->nullable()->after('qad_sync_history');
            $table->timestamp('wms_so_synced_at')->nullable()->after('wms_so_status');
            $table->text('wms_so_failure_reason')->nullable()->after('wms_so_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['wms_so_status', 'wms_so_synced_at', 'wms_so_failure_reason']);
        });
    }
};

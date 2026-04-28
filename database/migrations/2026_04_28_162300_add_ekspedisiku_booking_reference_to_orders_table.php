<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('ekspedisiku_booking_attempt')->default(0)->after('ekspedisiku_shipment_id');
            $table->string('ekspedisiku_booking_reference')->nullable()->after('ekspedisiku_booking_attempt');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ekspedisiku_booking_attempt', 'ekspedisiku_booking_reference']);
        });
    }
};


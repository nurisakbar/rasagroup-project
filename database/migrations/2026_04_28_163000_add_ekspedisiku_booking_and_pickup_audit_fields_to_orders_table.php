<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('ekspedisiku_booking_created_at')->nullable()->after('ekspedisiku_booking_reference');
            $table->string('ekspedisiku_booking_status', 32)->nullable()->after('ekspedisiku_booking_created_at');
            $table->text('ekspedisiku_booking_last_error')->nullable()->after('ekspedisiku_booking_status');

            $table->timestamp('ekspedisiku_pickup_requested_at')->nullable()->after('ekspedisiku_booking_last_error');
            $table->string('ekspedisiku_pickup_status', 32)->nullable()->after('ekspedisiku_pickup_requested_at');
            $table->text('ekspedisiku_pickup_last_error')->nullable()->after('ekspedisiku_pickup_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'ekspedisiku_booking_created_at',
                'ekspedisiku_booking_status',
                'ekspedisiku_booking_last_error',
                'ekspedisiku_pickup_requested_at',
                'ekspedisiku_pickup_status',
                'ekspedisiku_pickup_last_error',
            ]);
        });
    }
};


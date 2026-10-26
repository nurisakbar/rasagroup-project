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
            $table->string('edms_notification_status', 30)->nullable()->after('finance_approved_by');
            $table->timestamp('edms_notified_at')->nullable()->after('edms_notification_status');
            $table->text('edms_notification_response')->nullable()->after('edms_notified_at');
            $table->text('edms_notification_error')->nullable()->after('edms_notification_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'edms_notification_status',
                'edms_notified_at',
                'edms_notification_response',
                'edms_notification_error',
            ]);
        });
    }
};

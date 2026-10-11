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
        Schema::create('external_qris_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 100)->unique()->comment('Partner reference number from 3rd party app');
            $table->string('faspay_reference_no', 100)->nullable()->index()->comment('Transaction reference number from Faspay');
            $table->string('company', 20)->default('rdi');
            $table->string('channel_code', 20)->default('836');
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('pending')->index(); // pending, paid, expired, failed
            $table->text('qr_image_url')->nullable();
            $table->text('qr_url')->nullable();
            $table->text('callback_url')->nullable()->comment('Webhook URL provided by 3rd party');
            $table->string('callback_token', 255)->nullable()->comment('Secret / authorization token for 3rd party webhook');
            $table->string('callback_status', 30)->default('pending')->index(); // pending, delivered, failed, none
            $table->integer('callback_http_status')->nullable();
            $table->text('callback_response_body')->nullable();
            $table->timestamp('callback_sent_at')->nullable();
            $table->unsignedInteger('callback_attempts')->default(0);
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('raw_generate_response')->nullable();
            $table->json('raw_notification_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_qris_transactions');
    }
};

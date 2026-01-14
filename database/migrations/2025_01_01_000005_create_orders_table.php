<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->uuid('user_id');
            $table->enum('order_type', ['regular', 'distributor'])->default('regular');
            $table->uuid('address_id')->nullable();
            $table->uuid('expedition_id')->nullable();
            $table->string('expedition_service', 50)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->text('shipping_address')->nullable();
            $table->string('payment_method', 50)->default('manual_transfer');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->enum('order_status', ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->text('notes')->nullable();
            $table->integer('points_earned')->default(0);
            $table->boolean('points_credited')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('set null');
            $table->index('order_number');
            $table->index('order_status');
            $table->index('payment_status');
            $table->index('order_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


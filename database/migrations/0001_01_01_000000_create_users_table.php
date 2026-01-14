<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('role', ['buyer', 'reseller', 'agent', 'warehouse', 'driippreneur', 'distributor', 'super_admin'])->default('buyer');
            $table->uuid('warehouse_id')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('no_npwp')->nullable();
            $table->enum('distributor_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('distributor_province_id', 2)->nullable();
            $table->string('distributor_regency_id', 4)->nullable();
            $table->text('distributor_address')->nullable();
            $table->integer('points')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

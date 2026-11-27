<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data sales milik distributor (dikelola sendiri oleh distributor dari akun belanjanya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_salespeople', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('distributor_id');
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('area', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['distributor_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_salespeople');
    }
};

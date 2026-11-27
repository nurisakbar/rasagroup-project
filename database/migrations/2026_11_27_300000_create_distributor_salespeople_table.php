<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data sales milik distributor (dikelola sendiri oleh distributor dari akun belanjanya).
 * Setiap sales wajib terikat ke satu distributor (distributor_id).
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
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['distributor_id', 'name']);
            $table->unique(['distributor_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_salespeople');
    }
};

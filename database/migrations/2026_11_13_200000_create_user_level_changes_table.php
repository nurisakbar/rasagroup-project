<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat perubahan level pembeli (Outlet / User Biasa <-> Reseller) oleh admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_level_changes', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('from_level', 30);
            $table->string('to_level', 30);
            $table->uuid('changed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_level_changes');
    }
};

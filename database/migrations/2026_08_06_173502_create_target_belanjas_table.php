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
        Schema::create('target_belanja', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('distributor_id')->index();
            $table->string('bulan_tahun', 7); // Format: YYYY-MM
            $table->decimal('jumlah_target', 15, 2)->default(0);
            $table->timestamps();

            // Foreign key to users
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['distributor_id', 'bulan_tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_belanja');
    }
};

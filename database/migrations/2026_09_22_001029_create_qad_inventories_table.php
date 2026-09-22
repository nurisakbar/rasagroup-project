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
        Schema::create('qad_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->index();
            $table->string('qad_location_code')->index();
            $table->string('lot_serial')->nullable()->index();
            $table->decimal('qty', 15, 2)->default(0);
            $table->date('expired_date')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            // Using unique constraint to easily upsert later
            $table->unique(['item_code', 'qad_location_code', 'lot_serial'], 'qad_inv_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qad_inventories');
    }
};

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
        Schema::create('distributor_category_discounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('distributor_id');
            $table->uuid('category_id');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->unique(['distributor_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_category_discounts');
    }
};

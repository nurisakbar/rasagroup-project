<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable()->unique();
            $table->string('name');
            $table->string('commercial_name')->nullable();
            $table->text('description')->nullable();
            $table->text('technical_description')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('weight')->default(500)->comment('Weight in grams');
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('brand');
            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


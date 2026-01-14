<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add new foreign key columns
            $table->uuid('brand_id')->nullable()->after('technical_description');
            $table->uuid('category_id')->nullable()->after('brand_id');

            // Add foreign keys
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // Drop old string columns
            $table->dropIndex(['brand']);
            $table->dropIndex(['category']);
            $table->dropColumn(['brand', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['brand_id', 'category_id']);

            $table->string('brand', 100)->nullable()->after('technical_description');
            $table->string('category', 100)->nullable()->after('brand');
            $table->index('brand');
            $table->index('category');
        });
    }
};


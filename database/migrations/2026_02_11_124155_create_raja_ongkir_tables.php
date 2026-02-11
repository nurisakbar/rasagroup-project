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
        Schema::create('raja_ongkir_provinces', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('raja_ongkir_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('province_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('raja_ongkir_provinces')->onDelete('cascade');
        });

        Schema::create('raja_ongkir_districts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('city_id');
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('raja_ongkir_cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raja_ongkir_districts');
        Schema::dropIfExists('raja_ongkir_cities');
        Schema::dropIfExists('raja_ongkir_provinces');
    }
};

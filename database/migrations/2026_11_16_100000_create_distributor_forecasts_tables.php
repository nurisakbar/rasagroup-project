<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forecast kebutuhan distributor per outlet rekrutan × produk, per bulan (satuan kecil: botol/pcs).
 * Satu forecast per distributor per tahun; setiap perubahan angka dicatat untuk audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_forecasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->unique(['distributor_id', 'year']);
        });

        Schema::create('distributor_forecast_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('forecast_id')->constrained('distributor_forecasts')->cascadeOnDelete();
            $table->foreignUuid('outlet_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            for ($month = 1; $month <= 12; $month++) {
                $table->unsignedInteger('qty_' . $month)->default(0);
            }
            $table->timestamps();

            $table->unique(['forecast_id', 'outlet_id', 'product_id'], 'distributor_forecast_lines_unique');
        });

        Schema::create('distributor_forecast_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('forecast_id')->constrained('distributor_forecasts')->cascadeOnDelete();
            $table->uuid('outlet_id');
            $table->uuid('product_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('old_qty');
            $table->unsignedInteger('new_qty');
            $table->uuid('changed_by')->nullable();
            $table->string('source', 10);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['forecast_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_forecast_revisions');
        Schema::dropIfExists('distributor_forecast_lines');
        Schema::dropIfExists('distributor_forecasts');
    }
};

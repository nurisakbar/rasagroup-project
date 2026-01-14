<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_cost', 10, 2)->default(1.00);
            $table->integer('est_days_min')->default(1);
            $table->integer('est_days_max')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default expeditions
        $expeditions = [
            ['code' => 'jne', 'name' => 'JNE Express', 'description' => 'Jalur Nugraha Ekakurir', 'base_cost' => 1.0, 'est_days_min' => 2, 'est_days_max' => 4],
            ['code' => 'jnt', 'name' => 'J&T Express', 'description' => 'Express Your Online Business', 'base_cost' => 0.95, 'est_days_min' => 2, 'est_days_max' => 5],
            ['code' => 'sicepat', 'name' => 'SiCepat Ekspres', 'description' => 'Your Reliable Delivery Partner', 'base_cost' => 0.9, 'est_days_min' => 1, 'est_days_max' => 3],
            ['code' => 'anteraja', 'name' => 'AnterAja', 'description' => 'Pasti Sampai', 'base_cost' => 0.85, 'est_days_min' => 2, 'est_days_max' => 4],
            ['code' => 'pos', 'name' => 'POS Indonesia', 'description' => 'Untuk Indonesia', 'base_cost' => 0.8, 'est_days_min' => 3, 'est_days_max' => 7],
        ];

        foreach ($expeditions as $exp) {
            DB::table('expeditions')->insert([
                'id' => (string) Str::uuid(),
                'code' => $exp['code'],
                'name' => $exp['name'],
                'description' => $exp['description'],
                'base_cost' => $exp['base_cost'],
                'est_days_min' => $exp['est_days_min'],
                'est_days_max' => $exp['est_days_max'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};


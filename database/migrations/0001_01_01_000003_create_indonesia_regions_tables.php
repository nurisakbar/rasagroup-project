<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create tables with Schema Builder for consistency
        if (!Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->string('id', 2)->primary();
                $table->string('name', 50)->nullable();
            });
        }

        if (!Schema::hasTable('regencies')) {
            Schema::create('regencies', function (Blueprint $table) {
                $table->string('id', 4)->primary();
                $table->string('province_id', 2);
                $table->string('name', 50)->nullable();
                
                $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
                $table->index('province_id');
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->string('id', 7)->primary();
                $table->string('regency_id', 4);
                $table->string('name', 50)->nullable();
                
                $table->foreign('regency_id')->references('id')->on('regencies')->onDelete('cascade');
                $table->index('regency_id');
            });
        }

        if (!Schema::hasTable('villages')) {
            Schema::create('villages', function (Blueprint $table) {
                $table->string('id', 10)->primary();
                $table->string('district_id', 7);
                $table->string('name', 100)->nullable();
                
                $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
                $table->index('district_id');
            });
        }

        // Import data from SQL file if it exists (for MySQL)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $sqlFile = database_path('seeders/wilayah_administratif_indonesia.sql');
            
            if (file_exists($sqlFile)) {
                // Read SQL file and extract only INSERT statements
                $sql = file_get_contents($sqlFile);
                
                // Extract and execute INSERT statements for each table
                $this->executeInsertStatements($sql, 'provinces');
                $this->executeInsertStatements($sql, 'regencies');
                $this->executeInsertStatements($sql, 'districts');
                $this->executeInsertStatements($sql, 'villages');
            }
        }
    }

    private function executeInsertStatements(string $sql, string $table): void
    {
        // Find all INSERT statements for this table
        $pattern = "/INSERT INTO [`\"]?{$table}[`\"]? .*?;/s";
        preg_match_all($pattern, $sql, $matches);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $insertStatement) {
                try {
                    DB::unprepared($insertStatement);
                } catch (\Exception $e) {
                    // Ignore duplicate entry errors
                    if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                        throw $e;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};

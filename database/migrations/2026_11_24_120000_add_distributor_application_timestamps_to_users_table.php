<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dipakai alur pengajuan distributor (form buyer, daftar pengajuan admin, halaman status).
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'distributor_applied_at')) {
                $table->timestamp('distributor_applied_at')->nullable()->after('distributor_status');
            }
            if (! Schema::hasColumn('users', 'distributor_approved_at')) {
                $table->timestamp('distributor_approved_at')->nullable()->after('distributor_applied_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['distributor_applied_at', 'distributor_approved_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

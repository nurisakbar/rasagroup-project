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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('driippreneur_status', ['pending', 'approved', 'rejected'])->nullable()->after('distributor_address');
            $table->string('driippreneur_province_id', 2)->nullable()->after('driippreneur_status');
            $table->string('driippreneur_regency_id', 4)->nullable()->after('driippreneur_province_id');
            $table->text('driippreneur_address')->nullable()->after('driippreneur_regency_id');
            $table->timestamp('driippreneur_applied_at')->nullable()->after('driippreneur_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'driippreneur_status',
                'driippreneur_province_id',
                'driippreneur_regency_id',
                'driippreneur_address',
                'driippreneur_applied_at',
            ]);
        });
    }
};

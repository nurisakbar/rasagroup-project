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
            $table->string('sales_code')->nullable()->after('qad_customer_code');
        });

        // Modify the role enum
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'reseller', 'agent', 'warehouse', 'driippreneur', 'distributor', 'super_admin', 'sales') DEFAULT 'buyer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the role enum
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'reseller', 'agent', 'warehouse', 'driippreneur', 'distributor', 'super_admin') DEFAULT 'buyer'");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sales_code');
        });
    }
};

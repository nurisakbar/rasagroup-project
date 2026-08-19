<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','reseller','agent','warehouse','driippreneur','distributor','super_admin','sales','ecommerce','brand_marketing','finance','sales_admin','customer_service','it_application','inventory_manager') DEFAULT 'buyer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','reseller','agent','warehouse','driippreneur','distributor','super_admin','sales','ecommerce','brand_marketing','finance') DEFAULT 'buyer'");
    }
};

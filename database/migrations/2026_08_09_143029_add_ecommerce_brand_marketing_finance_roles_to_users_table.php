<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'reseller', 'agent', 'warehouse', 'driippreneur', 'distributor', 'super_admin', 'sales', 'ecommerce', 'brand_marketing', 'finance') DEFAULT 'buyer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'reseller', 'agent', 'warehouse', 'driippreneur', 'distributor', 'super_admin', 'sales') DEFAULT 'buyer'");
    }
};

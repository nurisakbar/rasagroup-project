<?php
$dir = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/database/migrations/';
$files = scandir($dir);
$migrationFile = '';

foreach ($files as $file) {
    if (strpos($file, 'add_outlet_to_role_enum_in_users_table.php') !== false) {
        $migrationFile = $dir . $file;
        break;
    }
}

if ($migrationFile) {
    $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'outlet' to the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','reseller','agent','warehouse','driippreneur','distributor','super_admin','sales','ecommerce','brand_marketing','finance','sales_admin','customer_service','it_application','inventory_manager', 'outlet') DEFAULT 'buyer'");
    }

    public function down(): void
    {
        // Remove 'outlet' from the enum (if needed, though standard practice is to leave it if it's safe or redefine without it)
        // If we rollback, we must ensure no user actually has 'outlet' before redefining, 
        // but for simplicity, we just redefine without 'outlet'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer','reseller','agent','warehouse','driippreneur','distributor','super_admin','sales','ecommerce','brand_marketing','finance','sales_admin','customer_service','it_application','inventory_manager') DEFAULT 'buyer'");
    }
};
PHP;
    file_put_contents($migrationFile, $content);
    echo "Migration updated.\n";
} else {
    echo "Migration not found.\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (DB::select('SHOW TABLES') as $table) {
    $tableName = array_values((array)$table)[0];
    foreach (DB::select("SHOW COLUMNS FROM $tableName") as $col) {
        if (stripos($col->Field, 'limit') !== false) {
            echo "$tableName.$col->Field\n";
        }
    }
}

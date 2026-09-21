<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$stats = app(\App\Services\MasterSync\QadHubSyncService::class)->syncAllStocks();
echo "Sync stats: " . json_encode($stats) . "\n";

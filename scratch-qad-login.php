<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qidApiService = app(\App\Services\QidApiService::class);
$token = $qidApiService->login();
echo "Token: " . var_export($token, true) . "\n";

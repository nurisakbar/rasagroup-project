<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = "https://development-qadwebapi.rasagroupoffice.com";
$username = "user.zoho";
$password = "Rasagroup@2025!";
$appsId = "86770460-cfd3-11ee-b044-d747f56a0d39";

echo "Testing login WITHOUT /api...\n";
$response1 = Http::post("{$baseUrl}/authorization/login", [
    'username' => $username,
    'password' => $password,
    'appsId'   => $appsId,
]);
echo "Status: " . $response1->status() . "\n";

echo "\nTesting login WITH /api...\n";
$response2 = Http::post("{$baseUrl}/api/authorization/login", [
    'username' => $username,
    'password' => $password,
    'appsId'   => $appsId,
]);
echo "Status: " . $response2->status() . "\n";

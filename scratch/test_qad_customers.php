<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qad = app(\App\Services\QadService::class);
echo "Testing listCustomer:\n";
$list = $qad->listCustomer(['limit' => 2]);
print_r(array_slice($list['data'] ?? $list ?? [], 0, 2));

echo "\nTesting getQadCustomer:\n";
$qadCustomer = $qad->getQadCustomer(['limit' => 2]);
print_r(array_slice($qadCustomer['data'] ?? $qadCustomer ?? [], 0, 2));

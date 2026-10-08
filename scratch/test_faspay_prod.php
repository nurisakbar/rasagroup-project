<?php
require '/var/www/rasaconnect.com/vendor/autoload.php';
$app = require_once '/var/www/rasaconnect.com/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new \App\Services\FaspaySnapService('mcr');
$order = new \stdClass();
$order->order_number = 'W' . date('ymdHis');
$order->company = 'mcr';
$order->user = new \stdClass();
$order->user->phone = '081234567890';

$response = $service->generateQris($order, 106000.00);
echo json_encode($response, JSON_PRETTY_PRINT);

<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qad = app(\App\Services\QadService::class);
$customers = $qad->getQadCustomer(['limit' => 10000]);

$md = "# Data Customer QAD\n\n";
$md .= "| Code | Name | City | Country | Currency | Terms | Address | Taxable | Cust Type |\n";
$md .= "|---|---|---|---|---|---|---|---|---|\n";

$data = $customers['data'] ?? $customers;
if (!is_array($data)) {
    echo "No array returned.\n";
    exit;
}

foreach ($data as $c) {
    $code = $c['customer_code'] ?? '-';
    $name = str_replace('|', '\\|', $c['name'] ?? '-');
    $city = str_replace('|', '\\|', $c['city'] ?? '-');
    $country = str_replace('|', '\\|', $c['country'] ?? '-');
    $curr = str_replace('|', '\\|', $c['currency'] ?? '-');
    $terms = str_replace('|', '\\|', $c['terms'] ?? '-');
    $addr = str_replace('|', '\\|', trim(($c['address1'] ?? '').' '.($c['address2'] ?? '').' '.($c['address3'] ?? '')));
    $tax = str_replace('|', '\\|', $c['taxable'] ?? '-');
    $type = str_replace('|', '\\|', $c['cust_type'] ?? '-');
    
    $md .= "| $code | $name | $city | $country | $curr | $terms | $addr | $tax | $type |\n";
}

file_put_contents(__DIR__.'/../qad_customers.md', $md);
echo "Berhasil menyimpan ".count($data)." data customer ke qad_customers.md\n";

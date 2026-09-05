<?php
$source = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/success.blade.php';
$dest = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/distributor/orders/success.blade.php';

$content = file_get_contents($source);

// Replace routes for distributor
$content = str_replace(
    "route('buyer.orders.show', \$order)",
    "route('distributor.orders.show', \$order)",
    $content
);

$content = str_replace(
    "route('products.index')",
    "route('distributor.orders.products')",
    $content
);

file_put_contents($dest, $content);
echo "Successfully copied success UI to distributor.\n";

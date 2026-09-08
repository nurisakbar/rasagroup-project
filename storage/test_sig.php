<?php
$publicKeyPath = '/Applications/MAMP/htdocs/rasagroup/faspay_public_key 2.pem';
$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
var_dump($publicKey);
$details = openssl_pkey_get_details($publicKey);
echo "Bits: " . $details['bits'] . "\n";

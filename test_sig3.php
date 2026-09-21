<?php
$stringToSign = "POST:/v1.0/transfer-va/inquiry:ede6e34611ecb4b3786a1720a1ec678c2c1bb3a53f955507e11a71ace06cdf0d:2026-09-07T07:24:59+07:00";
$signature = "tlADetuS62PZFCtPy612Z+oqcqKDldaW1VquEHoZtolJflW8Jf1rV2+3H9VZnXLtEKzMtisxk3cJwe4ZCjBDTgZxUDqltgfy10f0hzh8FvT2J3cqcJC6rpjY2okhoK7E1KFxcL7jX3C2DN0Sn1B3qwV8Djk/eFu1EubIoKNdJkFJypM0hwSGUz+2cLO5w6IGK+Awimc7IhGoIAUjvYANMGSP3h5xtNOmTPMf+55iztezEFNYABL9tOafSADWGrg6Z3Nj7psbxOajp3jbNaTnunFob8UG10qcxzXIZ3zos0k0QT+VcK3A6/ZqJpMYWF8bWtOMak62loRwUbaYWJCadQ==";

$keys = [
    "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/faspay_public_key 3.pem",
    "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/storage/app/faspay_public.pem",
    "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/37020_server.crt"
];

foreach ($keys as $path) {
    if (!file_exists($path)) {
        echo "Missing: $path\n";
        continue;
    }
    $pub = openssl_pkey_get_public(file_get_contents($path));
    if (!$pub) {
        echo "Bad key: $path\n";
        continue;
    }
    $res = openssl_verify($stringToSign, base64_decode($signature), $pub, OPENSSL_ALGO_SHA256);
    echo basename($path) . ": " . $res . "\n";
    if ($res !== 1) {
        while ($msg = openssl_error_string()) {
            echo "  " . $msg . "\n";
        }
    }
}

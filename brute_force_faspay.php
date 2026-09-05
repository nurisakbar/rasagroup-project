<?php
$publicKeyPath = '/Applications/MAMP/htdocs/rasagroup/UAT OK/faspay_public_key.pem';
$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));

$signature = "BP4ru29JSHWRgRKeVhU8txWKjayWCGBHKIhf6nNosEvVRPCnFEqBsNzcKA0vfs19Sb71Lm8bayW7mrx5yIeS1A8NtuRcyF+L5+GvxYead80htKAJVLqZMKWs25u7eephpY3g/OoimcDkJpm9UQA8jkbWL8oc+U6OWwEn/Acz8wJFpKCwxFbIXDqrzgIbv317LyELXyN+1/p7LnpRmjh4wdeu7ozTepVBGvXrnq+MjVkQPm3YLtE+pNqkhgXeDOv8iP5eYZi4KLyNwiYDzFwFxYdytSPaPhCtlnIKPpLwoq+ZiLe2Mp9mgihwtjMGKzQml62uwRZH4r2GpoJHJrI1iw==";

$bodyRaw = '{"partnerServiceId":"37020002","customerNo":"60811002","virtualAccountNo":"3702000260811002","inquiryRequestId":"840079c8-150e-4c5a-a02e-67b927a4"}';
$timestamp = "2026-08-11T14:31:10+07:00";

$bodyHashLower = strtolower(hash('sha256', $bodyRaw));
$bodyHashUpper = strtoupper(hash('sha256', $bodyRaw));
$bodyHashRaw = hash('sha256', $bodyRaw);
$bodyMd5 = md5($bodyRaw);

$methods = ["POST"];
$paths = [
    "/api/faspay/snap/inquiry",
    "api/faspay/snap/inquiry",
    "/api/faspay/snap/inquiry/",
    "/faspay/snap/inquiry",
    "https://dev.rasaconnect.com/api/faspay/snap/inquiry",
    "http://dev.rasaconnect.com/api/faspay/snap/inquiry",
    "dev.rasaconnect.com/api/faspay/snap/inquiry",
    ""
];

$tokens = [
    "",
    " ",
    "null",
    "77001",   // channel-id
    "37020",   // x-partner-id
    "46184262175345217477394921910338077" // x-external-id
];

$hashes = [
    $bodyHashLower,
    $bodyHashUpper,
    $bodyHashRaw,
    $bodyMd5,
    $bodyRaw, // no hash
    ""
];

$formats = [
    "%s:%s:%s:%s:%s", // method:path:token:hash:timestamp (SNAP standard)
    "%s:%s:%s:%s",    // method:path:hash:timestamp
    "%s|%s|%s|%s|%s", // pipe delimited
    "%s|%s|%s|%s",
    "%s:%s::%s:%s",   // empty token
    "%s:%s:%s"        // maybe just method:path:timestamp
];

$success = false;

foreach ($methods as $m) {
    foreach ($paths as $p) {
        foreach ($tokens as $t) {
            foreach ($hashes as $h) {
                // Try format: Method:Path:Token:Hash:Timestamp
                $str1 = "{$m}:{$p}:{$t}:{$h}:{$timestamp}";
                if (openssl_verify($str1, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1) {
                    echo "MATCH FOUND!\n$str1\n"; $success = true;
                }
                
                // Try format: Method:Path:Hash:Timestamp
                $str2 = "{$m}:{$p}:{$h}:{$timestamp}";
                if (openssl_verify($str2, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1) {
                    echo "MATCH FOUND!\n$str2\n"; $success = true;
                }
            }
        }
    }
}

if (openssl_verify($bodyRaw, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1) {
    echo "MATCH FOUND! Raw Body!\n"; $success = true;
}

if (!$success) {
    echo "No matches found with standard algorithms.\n";
}
